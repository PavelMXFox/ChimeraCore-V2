<?php
use fox\config;
use fox\baseModule_Messenger;

require_once($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/fox_api.php");

class mod_messenger_telegram extends baseModule_Messenger
{
    public static $title="Telegram messenger module";
    public static $description="Модуль мессенджера Telegram";
    public static $version="1.0.0";
    public static $type="messenger";
    
    // При уставновке этого флага пользователь должен написать системе первым. В этом случае при связывании
    public static $reqiureJoin=true;
    public static $reqireConfirmation=false;
    
}


?>