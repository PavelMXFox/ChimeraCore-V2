<?php
namespace fox;

class baseModule_Logger extends baseModule
{
    public static $title="Basic Logger Module Template";
    public static $description="Базовый класс логгера модуля-заглушки ";
    public static $version="2.0.0";
    public static $type="log";
    
    public function sendEvent($event)
    {
        return false;
    }
    
}
