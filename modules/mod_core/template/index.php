<?php 
ob_start();

use fox\fail2ban;
use fox\urlParcer;


   require_once($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/fox_api.php");

    $sql = new coreSql();
    $request = new coreUrlParcer();
    $mod_name = $request->module;
    $modules = new coreModules($sql);
    $modules->loadModules($sql);
    
    if($mod_name == "api")
    {
        if (!fail2ban::checkFail(urlParcer::getClientIP())) {
            coreErrorPage::show("500.91");
            exit;
        }

        if ($request->function != 'cfx') {
            coreErrorPage::show("404.94");
            exit;
        }
        
        try {
            
            $session = new fox\session_API();
            $request->session = $session;
            $message = \fox\APIMessage::decode(\fox\common::getVal("message",null,true));
            $session->loadMessage($message);

            
            if (!$session->check()) {
                coreErrorPage::show("403.92", null,true,true);
                exit;
            }
            
            $req = explode("/", $message->packet->command,2);
            if (count($req) == 0) {
                throw new Exception("Invalid command");
            } elseif (count($req) == 1) {
                $request->module="core";
                $request->function=$req[0];
            } else {
                $request->module=$req[0];
                $request->function=$req[1];
            }
            $request->parameters=$message->packet->data;
            
            
            if ($request->module=='core' && $request->function=='test') {
                print json_encode($message->reply(["status"=>"OK","message"=>"Communication test passed."]));
                exit;
            }
            
            $module = $modules->getClass($request->module);
            if ($module && ($module::$globalAccessKey === null || $session->checkAccess($module::$globalAccessKey, $module->instance))) {
                $request->moduleClass = $module;
                $fnx = "\\".get_class($module)."_api\\".preg_replace("/[^0-9a-zA-Z_-]/","c", $request->function);
                if (function_exists($fnx)) {
                   $res = $fnx($request);
                } else {
                    $res = $module->apiCall($request);
                }
                
                
                if (gettype($res) == 'array' || gettype($res)=='object') {
                } elseif ($res===false || $res===null) {
                    coreErrorPage::show("404.92", null,true,true);
                    exit;
                } else {
                    $res=["result"=>$res];    
                }
                print json_encode($message->reply($res));
                
            } else {
                $coreAPI->logger->addEvent("core", "Unauthorized request 403.9 from '".$request::getClientIP()."'",coreLogger::sevWarning,"Unauthorized",inet_aton($request::getClientIP()),(array)$request,0);
                coreErrorPage::show("404.93", null,true,true);
                exit;
            }
        } catch (\Exception $e) {
            trigger_error($e->getCode().": ".$e->getMessage()." in ".$e->getFile()." at line ".$e->getLine(), E_USER_WARNING);
            coreErrorPage::show("404.92", null,true,true);
            exit;
        }
        exit;
    } else {
        $session = new coreSessionWeb($sql);
        $request->session = $session;
    }
   
    if (!fail2ban::checkFail(urlParcer::getClientIP()) && !$session->check()) {
        coreErrorPage::show("500.91");
        exit;
    }
    
	if ($mod_name == "ajax")
	{
		if ($request->function == "auth")
		{
		    $module = $modules->getClass("core");
		    $module->authAjaxPage();
		} elseif ($session->check())
		{
			$request->module = $request->function;
			
			if (array_key_exists(0,$request->parameters)) { 
					$request->function = $request->parameters[0]; 
					if (count($request->parameters) > 1)
   				{
	   				array_splice($request->parameters,0,-(count($request->parameters)-1));
   				} else {
						$request->parameters = [];   
   				}
			}	else {$request->function = null; }	
			
			if (!$modules->isLoaded($request->module)) {print "403.5: Unauthorized"; header('HTTP/1.0 403 Unauthorized', true,403); exit; }
				$module = $modules->getClass($request->module);	
				if ($module::$globalAccessKey === null || $session->checkAccess($module::$globalAccessKey, $module->instance)) {
				    $module->ajaxPage();
				} else {
				    $coreAPI->logger->addEvent("core", "Unauthorized request from '".$request::getClientIP()."'",coreLogger::sevWarning,"Unauthorized",inet_aton($request::getClientIP()),(array)$request,0);
				    coreErrorPage::show("403.7", null,true);
				}
		} else {
		    $coreAPI->logger->addEvent("core", "Unauthorized request 403.8 from '".$request::getClientIP()."'",coreLogger::sevWarning,"Unauthorized",inet_aton($request::getClientIP()),(array)$request,0);
		    coreErrorPage::show("403.8", null,true);
		}
		exit;		
	}  elseif($mod_name == "hook") {
	    $request->shift();
	    $module = $modules->getClass($request->module);
	    if ($module) {
	        if (!$module->webHook($request)) {
	            coreErrorPage::show("404.17", null,true,true);
	        }
	    } else {
	        $coreAPI->logger->addEvent("core", "Invalid request 404.11 from '".$request::getClientIP()."'",coreLogger::sevWarning,"invalidRequest",inet_aton($request::getClientIP()),(array)$request,0);
	        coreErrorPage::show("404.11", null,true);
	        
	        print "404.11: Not found"; header('HTTP/1.0 404 Not found', true,404); exit;
	    }
	    exit;
    } elseif($mod_name == "js" || $mod_name == "css" || $mod_name == "img" || $mod_name=="fnt") {
		if (!$modules->isLoaded($request->function)) {
		    $coreAPI->logger->addEvent("core", "Invalid request 404.10 from '".$request::getClientIP()."'",coreLogger::sevWarning,"invalidRequest",inet_aton($request::getClientIP()),(array)$request,0);
		    coreErrorPage::show("404.10", null,true);
		}
		coreModules::getAuxFiles($modules->modules[$request->function]->loadName, $mod_name, $request->parameters[0]);
		exit;
    } elseif ($mod_name=='login') {
        $mod_name = coreConfig::get("defaultLoginModule");
        if (!$mod_name)
        {
            $mod_name="core";
        }
        
        if (!$modules->isLoaded($mod_name)) {
            $mod_name=='core';
        }
        
        $module = $modules->getClass($mod_name);
        $module->loginPage();
        return;
        
    } elseif (!$mod_name)
	{
		$mod_name = coreConfig::get("defaultModule");
		if (!$mod_name)
		{
			$mod_name="core";	
		}
	}
	
	if (!$modules->isLoaded($mod_name) && $mod_name!='login')
	{
	    $coreAPI->logger->addEvent("core", "Invalid request 404.13 from '".$request::getClientIP()."'",coreLogger::sevWarning,"invalidRequest",inet_aton($request::getClientIP()),(array)$request,0);
	    coreErrorPage::show("404.13", null);
	}
	
	$mod_call_name = "mod_".$mod_name;	
	$mod_call_name = "mod_".$modules->modules[$mod_name]->loadName;// "mod_".$mod_name;	
	
	if ($mod_call_name::$authRequred != false || $mod_name=='login')
	{
		if (!$session->check() || $mod_name=='login')
		{
			$mod_name = coreConfig::get("defaultLoginModule");
			if (!$mod_name)
			{
				$mod_name="core";	
			}	
	
			if (!$modules->isLoaded($mod_name)) { 
                coreErrorPage::show("404.12", "Not found");
 				exit;
			}
			
			$module = $modules->getClass($mod_name);
			$module->loginPage();
			return;	
		}
	}
	
	$module = $modules->getClass($mod_name);
	
	if ($module::$globalAccessKey === null || $session->checkAccess($module::$globalAccessKey, $module->instance)) {
	    define("currModule",$module->instance);
	    $module->mainPage();
	} else {
	    $coreAPI->logger->addEvent("core", "Unauthorized request 403.6 from '".$request::getClientIP()."'",coreLogger::sevWarning,"Unauthorized",inet_aton($request::getClientIP()),(array)$request,0);
	    coreErrorPage::show("403.6");
	}
	
	

?>