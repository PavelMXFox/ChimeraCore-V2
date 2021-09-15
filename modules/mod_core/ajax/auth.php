<?php 

use fox\auth;
use fox\authTicket;
use fox\foxException;
use fox\xcrypt;

/**
 * Module core/ajax/auth
 * @copyright MX STAR LLC 2020
 * @version 2.0.0
 * @author Pavel Dmitriev
 * @desc Auth and user password manipulation block
 *
 **/

global $request, $session, $coreAPI;

switch ($request->parameters[0]) {
    case "login":
        $username = getVal("username");
        $password = getVal("password");
        if ($session->doAuth($username, $password))
        {
            print json_encode(["status"=>"OK","cookie_name"=>$session->cookieName,"cookie"=>$session->cookieVal, "cookie_expire"=>$session->cookieTimeout]);
        } else {
            sleep(rand(2,5));   
            print json_encode(["status"=>"ERR","message"=>"Auth failed"]);
            fox\fail2ban::addFail(fox\urlParcer::getClientIP(), "auth_login", $coreAPI->SQL);
            exit;
        }  
        break;
    case "logoff":
        if ($session->check() && $session->close())
        {
            print json_encode(["status"=>"OK","cookie_name"=>"mxsauthvt"]);
        } else {
            print json_encode(["status"=>"ERR", "message"=>"Logout failed"]);
        }
        break;
        
    case "recover":
        $login = getVal("login");
        $email=getVal("email");
                
        $user = fox\user::recoverSearch($login, $email);
        if ($user) {
            try {
                            
                if (!fox\net::validateEMail($user->eMail)) {
                    throw new ErrorException("invalid user eMail",502);
                }
                
                if (!fox\auth::preCheckChangePassword($user, $coreAPI->SQL)) {
                    throw new Exception("authType is not ready for passwordRecovery for user=".$user->login,503);
                }
                
                $at = new authTicket();
                $at->payload=["userId"=>$user->id, "username"=>$user->login, "operation"=>"passwordRecovery"];
                $at->expireDays(3);
                $at->save();
                
                $coreAPI->logger->addEvent("core_auth", "Password PreChange Request for '".$user->login,fox\logger::sevInfo,"pwdChangeSent",inet_aton(fox\urlParcer::getClientIP()),["ip"=>fox\urlParcer::getClientIP()],0);
                
                $html="<div id='preheader' style='display:none;'>Восстановление пароля для ".fox\config::get("svcName")."</div>
<div  style='font-family: sans-serif; color: #02394E'>
Добрый день, <span style='color: #ff6f0f'>".((null===$user->fullName || $user->fullName=='')?$user->eMail:$user->fullName)."</span>!<br/>
Вы (или кто-то другой) запросили восстановление пароля на сайте <a style='color:  #ff6f0f; text-decoration: underline;' href='".fox\config::get("sitePrefix")."'>".fox\config::get("svcName")."</a><br/>
<br/>
Для восстановления пароля укажите код <span style='color: #ff6f0f'>".$at->uidPrint."</span> в форме восстановления<br/>
или перейдите по <a style='color:  #ff6f0f; text-decoration: underline;' href='
".fox\config::get("sitePrefix")."/login/recover?code=".$at->uid."&login=".$user->login."'>ссылке</a><br/>
<br/>
Если восстановление пароля не требуется либо письмо было отправлено по ошибке - просто проигнориуйте его.
<br/>
    
Отвечать на это письмо не нужно, так как оно отправлено автоматически.
<br/>
<br/>
С уважением,<br/>
Команда <span style='color: #ff6f0f'>".fox\config::get("svcName")."</span>
    
</div>
";
                $msg = new fox\mailMessage();
                $msg->addRecipient($user);
                $msg->subject="Восстановление пароля";
                $msg->bodyHTML=$html;
                $msg->direction="TX";
                $msg->fromRobot=true;
                $msg->account=fox\config::get("infoEMailAccount");
                $msg->mailFrom=fox\config::get("infoEMail");
                $msg->send();
            } catch (Exception $e) {
                sleep(rand(2,5));
                trigger_error($e->getCode().":".$e->getMessage(), E_USER_WARNING);
            }
        } else {
            sleep(rand(2,5));
            print json_encode(["status"=>"ERR", "message"=>"User not found","code"=>7504]);
            fox\fail2ban::addFail(fox\urlParcer::getClientIP(), "auth_recover", $coreAPI->SQL);
            exit;
        }

        print json_encode(["status"=>"OK", "message"=>""]);
        
        break;
        
    case "change":
        $code = getVal("code","0-9");
        $login=getVal("login");
        $passwd=getVal("passwd");
        
        
        try {
            $at = new authTicket($code);
            if ($at->payload->operation!="passwordRecovery") {
                print json_encode(["status"=>"ERR","message"=>"Invalid code or login","code"=>715]);
                fox\fail2ban::addFail(fox\urlParcer::getClientIP(), "auth_recover", $coreAPI->SQL);
                exit;
            }
        } catch (\Exception $e) {
            sleep(rand(2,5));
            print json_encode(["status"=>"ERR","message"=>"Invalid code or login","code"=>711]);
            fox\fail2ban::addFail(fox\urlParcer::getClientIP(), "auth_recover", $coreAPI->SQL);
            exit;
        }
                
        $user = new fox\user($at->payload->userId);
        
        if (!$user) {
            sleep(rand(2,5));
            print json_encode(["status"=>"ERR","message"=>"Invalid code or login","code"=>712]);
            fox\fail2ban::addFail(fox\urlParcer::getClientIP(), "auth_recover", $coreAPI->SQL);
            exit;
        }
        
        if (strtolower($login) != strtolower($user->login)) {
            sleep(rand(2,5));
            print json_encode(["status"=>"ERR","message"=>"Invalid code or login","code"=>713]);
            fox\fail2ban::addFail(fox\urlParcer::getClientIP(), "auth_recover", $coreAPI->SQL);
            exit;
        }
        
        $at->delete();
        
        try {
            auth::changePassword($user, $passwd, $coreAPI->SQL);
            $coreAPI->logger->addEvent("core_auth", "Password Changed for '".$user->login."'",fox\logger::sevInfo,"pwdChanged",inet_aton(fox\urlParcer::getClientIP()),["userid"=>$user->id, "userLogin"=>$user->login, "email"=>$user->eMail, "ip"=>fox\urlParcer::getClientIP()],0);
            print json_encode(["status"=>"OK", "message"=>""]);
        } catch (Exception $e) {
            sleep(rand(2,5));
            print json_encode(["status"=>"ERR", "message"=>"Unable to change password","code"=>714]);
            trigger_error($e->getMessage(), E_USER_WARNING);
            $coreAPI->logger->addEvent("core_auth", "Password Change Failed for '".$user->login,fox\logger::sevError,"pwdChangeFailed",inet_aton(fox\urlParcer::getClientIP()),["userid"=>$user->id, "userLogin"=>$user->login, "ip"=>fox\urlParcer::getClientIP(),"message"=>$e->getMessage()],0);
            exit;
        }
        break;

    case "register":
               
        try {
            $payload=[];
            $payload["module"] = fox\config::get("registerModule");
            $payload["login"] = getVal("login");
            $payload["fullName"] = getVal("fullname");
            $payload["eMail"] = getVal("email");
            $payload["passwd"] = fox\xcrypt::encrypt(getVal("password",null,true));
            $payload["code"] = getVal("code","0-9");
            $payload["accept"] = getVal("accept");
            $payload["operation"]="userRegister";
            $payload["groups"] = [];
            
            if (!fox\modules::isModuleInstalled("auth_".$payload["module"])) {
                foxException::throw("ERR","Invalid configuration",801);
            }
            
            if (!fox\net::validateEMail($payload["eMail"])) {
                foxException::throw("WARN","Invalid eMail",703);
            }


            if (!empty($payload["code"])) {
                try {
                    $atx = new authTicket($payload["code"]);
                    if ($atx->payload->operation!='userInvite') { foxException::throw("WARN","User not invited",709); }
                    if ($atx->payload->eMailRestrict==true && strtolower($payload["eMail"]) != strtolower($atx->payload->eMail)) { foxException::throw("WARN","User not invited",710); }
                    $payload["groups"] = $atx->payload->groups;
                } catch (Exception $e){
                    foxException::throw("WARN","User not invited",708);
                }
            } elseif (fox\config::get("allowRegister")!==true) {
                foxException::throw("WARN","User not invited",707);
            }

            $auth_mi = new fox\moduleInfo("auth_".$payload["module"]);
            $auth_m=$auth_mi->newClass();
            
            if ($auth_m::$type!='auth') {
                foxException::throw("ERR","invalid configuration",802);
            }
            
            if (fox\user::recoverSearch($payload["login"], null, $payload["module"],$coreAPI->SQL)) {
                foxException::throw("WARN","User already registered",702);
            }
            
            if (!$auth_m->preCheckRegister($payload["login"],$payload["eMail"])) {
                foxException::throw("WARN","Rejected",706);
            }
            
            if ($payload["accept"] !=='accept') {
                foxException::throw("WARN","PDP Not accepted",705);
            }
            
            
            $at=new fox\authTicket();
            $at->payload = $payload;
            $at->expireDays(1);
            $at->save();
            
            $coreAPI->logger->addEvent("core_auth", "Register request for '".$payload["login"],fox\logger::sevInfo,"userRegister",inet_aton(fox\urlParcer::getClientIP()),["ip"=>fox\urlParcer::getClientIP(),"login"=>$payload["login"], "email"=>$payload["eMail"]],0);
            
            $html="<div id='preheader' style='display:none;'>Регистрация пользователя на ".fox\config::get("svcName")."</div>
<div  style='font-family: sans-serif; color: #02394E'>
Добрый день, <span style='color: #ff6f0f'>".(empty($payload["fullName"])?$payload["eMail"]:$payload["fullName"])."</span>!<br/>
Вы (или кто-то другой) заполнили форму регистрации на сайте <a style='color:  #ff6f0f; text-decoration: underline;' href='".fox\config::get("sitePrefix")."'>".fox\config::get("svcName")."</a><br/>
<br/>
Ваши данные:<br/>
Логин: <span style='color: #ff6f0f'>".$payload["login"]."</span><br/>
E-Mail: <span style='color: #ff6f0f'>".$payload["eMail"]."</span><br/>
<br/>

Для завершения регистрации укажите код <span style='color: #ff6f0f'>".$at->uidPrint."</span> в форме подтверждения<br/>
или перейдите по <a style='color:  #ff6f0f; text-decoration: underline;' href='
".fox\config::get("sitePrefix")."/login/regconfirm?code=".$at->uid."&login=".$payload["login"]."'>ссылке</a><br/>
<br/>
Если регистрация не требуется либо письмо было отправлено по ошибке - просто проигнориуйте его.
<br/>
    
Отвечать на это письмо не нужно, так как оно отправлено автоматически.
<br/>
<br/>
С уважением,<br/>
Команда <span style='color: #ff6f0f'>".fox\config::get("svcName")."</span>
    
</div>
";
            $msg = new fox\mailMessage();
            $msg->addRecipient($payload["fullName"]."<".$payload["eMail"].">");
            $msg->subject="Подтверждение регистрации";
            $msg->bodyHTML=$html;
            $msg->direction="TX";
            $msg->fromRobot=true;
            $msg->account=fox\config::get("infoEMailAccount");
            $msg->mailFrom=fox\config::get("infoEMail");
            $msg->send();
            
            $atx->delete();
            print json_encode(["status"=>"OK", "message"=>""]);
            
            
            
        } catch (foxException $e) {
            sleep(rand(2,5));
            if ($e->getCode() >= 700 && $e->getCode()<900) {
                print(json_encode(["status"=>$e->getStatus(),"message"=>$e->getMessage(),"code"=>$e->getCode()]));
                fox\fail2ban::addFail(fox\urlParcer::getClientIP(),"auth_register");
            } else {
                trigger_error($e->getStatus().": ".$e->getCode().": ".$e->getMessage()." in ".$e->getFile()." at line ".$e->getLine(), E_USER_WARNING);
                print(json_encode(["status"=>"ERR2","message"=>"Internal server error","code"=>500]));
            }
            exit;
        } catch (Exception $e) {
            sleep(rand(2,5));
            trigger_error($e->getCode().": ".$e->getMessage()." in ".$e->getFile()." at line ".$e->getLine(), E_USER_WARNING);
            print(json_encode(["status"=>"ERR1","message"=>"Internal server error","code"=>500]));
            exit;
        }
        break;
        
    case "regconfirm":
        $code=getVal("code","0-9a-zA-Z");
        $login=getVal("login");
        
        try {
            try {
                $at = new authTicket($code);
                if ($at->payload->operation!="userRegister") {
                    foxException::throw("ERR","Invalid code or login",737);
                }
                
            } catch (\Exception $e) {
                foxException::throw("ERR", "invalid login or code", 731);
            }
                    
            if (strtolower($login) != strtolower($at->payload->login)) {
                foxException::throw("ERR", "invalid login or code", 732);
            }
        
            if (!fox\modules::isModuleInstalled("auth_".$at->payload->module)) {
                foxException::throw("ERR","Invalid configuration",801);
            }
            
            $auth_mi = new fox\moduleInfo("auth_".$at->payload->module);
            $auth_m=$auth_mi->newClass();
            
            if ($auth_m::$type!='auth') {
                foxException::throw("ERR","invalid configuration",802);
            }
            
            
            if (!$auth_m->preCheckRegister($at->payload->login,$at->payload->eMail)) {
                foxException::throw("WARN","Rejected",736);
            }

            $user = new fox\user();
            $user->login=$at->payload->login;
            $user->eMail=$at->payload->eMail;
            $user->fullName=$at->payload->fullName;

            
            
            if ($auth_m->register($user)) {
                $auth_m->changePassword($user, xcrypt::decrypt($at->payload->passwd));
                $at->delete();
                print json_encode(["status"=>"OK", "message"=>"","code"=>200]);
            } else {
                foxException::throw("ERR", "Internal server error", 724);
            }
            
        } catch (foxException $e) {
            sleep(rand(2,5));
            if ($e->getCode() >= 700 && $e->getCode()<900) {
                print(json_encode(["status"=>$e->getStatus(),"message"=>$e->getMessage(),"code"=>$e->getCode()]));
                fox\fail2ban::addFail(fox\urlParcer::getClientIP(),"auth_register");
            } else {
                trigger_error($e->getStatus().": ".$e->getCode().": ".$e->getMessage()." in ".$e->getFile()." at line ".$e->getLine(), E_USER_WARNING);
                print(json_encode(["status"=>"ERR","message"=>"Internal server error","code"=>500]));
            }
            exit;
        } catch (Exception $e) {
            sleep(rand(2,5));
            trigger_error($e->getCode().": ".$e->getMessage()." in ".$e->getFile()." at line ".$e->getLine(), E_USER_WARNING);
            print(json_encode(["status"=>"ERR","message"=>"Internal server error","code"=>500]));
            exit;
        }
        
        break;

    default:
        $coreAPI->logger->addEvent("core", "Invalid request from '".$request::getClientIP()."'",coreLogger::sevWarning,"invalidRequest",inet_aton($request::getClientIP()),(array)$request,0);
        coreErrorPage::show("404.15", "Not found",true);
        break;
}
?>