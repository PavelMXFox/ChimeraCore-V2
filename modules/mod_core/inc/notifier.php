<?php namespace fox;

class notifier {
    protected $modules=[];
    protected $sql;
    
    function __construct() {
        global $coreAPI;
        $this->sql = $coreAPI->SQL;
        $this->modules = modules::getModules("notify");
        if ($this->modules===false) { $this->modules =[]; }
        
        var_dump($this->modules);
    }
}