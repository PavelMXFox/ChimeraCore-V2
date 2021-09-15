<?php namespace fox;

class baseModule_Messenger extends baseModule
{
    public static $title="Basic Messenger Module Template";
    public static $description="Базовый класс уведомлялки модуля-заглушки ";
    public static $version="1.0.0";
    public static $type="notify";

    public static $reqiureJoin=false;
    public static $reqireConfirmation=false;
    
    public function send($to, $message, $subject=null, $account=null)
    {
        throw new \Exception("Function ". __METHOD__ ." not implemented yet");
    }
    
    public function getJoinLink(&$user, $TTL=3600) {
        throw new \Exception("Function ". __METHOD__ ." not implemented yet");
    }
    
    public function sendConfirmation(&$user, $TLL=3600) {
        throw new \Exception("Function ". __METHOD__ ." not implemented yet");
    }
    
    public function blockRecipient(&$user) {
        throw new \Exception("Function ". __METHOD__ ." not implemented yet");
    }
    
    public function registerWebhook() {
        throw new \Exception("Function ". __METHOD__ ." not implemented yet");
    }
    
    public function unregisterWebhook() {
        throw new \Exception("Function ". __METHOD__ ." not implemented yet");
    }
}

?>