<?php
use fox\config;
use fox\baseModule_Messenger;

require_once($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/fox_api.php");

class mod_messenger_email extends baseModule_Messenger
{
    public static $title="EMail messenger module";
    public static $description="Модуль мессенджера EMail";
    public static $version="1.0.0";
    public static $type="messenger";
    
    // При уставновке этого флага пользователь должен написать системе первым. В этом случае при связывании
    public static $reqiureJoin=false;
    public static $reqireConfirmation=true;
    
}


?>