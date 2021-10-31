<?php
namespace fox;
require_once 'coreAPI.php';

class auth
{
	public static function doAuth($authId, $authSecret=null, $authMethod=null,&$sql=null):?baseModule_Auth
	{
		foreach (modules::getModules("auth") as $mod_desc)
		{
			if (modules::loadModule($mod_desc->name))
			{
				$mod_name = "mod_".$mod_desc->name;
				$auth = null;
				$auth = new $mod_name();
				
				if ($auth->doAuth($authId, $authSecret, $authMethod,$sql)) {
					$auth->userRights = static::getUserRights($auth->user->id, $sql);

					return $auth;
				}		
			}		
		}


		return null;
	}	
	
	public static function checkAuth($userId,&$sql=null)
	{
	    try {
		  $user = new user($userId);
	    } catch (\Exception $e) {
	        trigger_error($e->getCode().": ".$e->getMessage()." in ".$e->getFile()." at line ".$e->getLine(), E_USER_WARNING);
	        return null;
	    }

		$authModule = "auth_".$user->authType;
		

		
		if (!modules::loadModule($authModule)) {return false; }
		$className="mod_".$authModule;
		$auth = new $className($className, $user,$sql);
		if ($auth->checkAuth($user))
		{
			$auth->userRights = static::getUserRights($auth->user->id, $sql);
			return $auth;		
		}	else {
		    return null; 
		}
	}
	
	public static function changePassword($user, $password, &$sql=null) {
	    if (gettype($user) != 'object') {
	        $user = new user($user);
	    }
	    
	    $authModule = "auth_".$user->authType;
	    
	    if (!modules::loadModule($authModule)) {return false; }
	    $className="mod_".$authModule;
	    $auth = new $className($className, $user,$sql);
	    
	    $auth->changePassword($user, $password);
	}
	
	public static function preCheckChangePassword($user, &$sql=null) {
	    global $coreAPI;
	    if (gettype($user) != 'object') {
	        $user = new user($user);
	    }
	    
	    $authModule = "auth_".$user->authType;
	    
	    if (!modules::loadModule($authModule)) {return false; }
	    $className="mod_".$authModule;
	    return class_exists($className) && $className::preCheckChangePassword($user);
	}
	static public function checkAccess($rule, $module="all", &$rights, $strict=false)
   {
		if ($strict==false && array_key_exists("all", $rights) &&  array_key_exists("isRoot", $rights["all"])) 
		{
			return true;
		} 
		elseif (array_key_exists("all", $rights) && array_key_exists($rule, $rights["all"]))
		{
			return true;
		} 
		elseif (array_key_exists($module, $rights) && array_key_exists($rule, $rights[$module]))
		{
			return true;		
		}
		
		return false;
   }
   
	static public function getUserRights($userId, &$sql=null) 
	{   
		if (empty($sql)) {$sql=new sql(); }
		$res = $sql->quickExec("SELECT `module`,`rule` from `tblGroupRightsLink` as `r` INNER join `tblUserGroupLink` as `g` on `g`.`groupId` = `r`.`groupId` where `g`.`userId` = '".$userId."'");
		$rights = [];
		while ($row=mysqli_fetch_assoc($res))
		{
			$rights[$row["module"]][$row["rule"]] = $row["rule"];
		}
		
		return $rights;
	}
}

?>