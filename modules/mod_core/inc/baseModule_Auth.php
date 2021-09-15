<?php

namespace fox;

class baseModule_Auth extends baseModule
{
    public ?user $user;
    public static $title="Basic Auth Module Template";
    public static $description="Базовый класс авторизатора модуля-заглушки ";
    public static $version="2.0.0";
    public static $type="auth";
    
    
    public $userRights=[];
    
    function __construct($instance=null){
        $this->user = new user();
        parent::__construct($instance);
    }
    
    public function doAuth($authId, $authSecret=null, $authMethod=null)
    {
        return false;
    }
    
    public function checkAuth(&$user=null)
    {
        return false;
    }
    
    public function preCheckChangePassword(&$user) {
        return false;
    }
    
    public function changePassword(&$user, $password) {
        return false;
    }
    
    public function preCheckRegister($login, $eMail) {
        return false;   
    }

    public function register(user &$user) {
        return false;
    }
    
}
