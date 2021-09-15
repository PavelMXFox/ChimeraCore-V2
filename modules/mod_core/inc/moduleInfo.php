<?php 
namespace fox;

class moduleInfo
{
    var $name;
    var $loadName;
    var $priority;
    var $type;
    var $version;
    var $desc;
    var $updateDate;
    var $installDate;
    var $id;
    var $loaded=false;
    protected $sql;
    
    
    function __construct($id=null, $noload = null,$sql=null)
    {
        if (isset($sql)) {$this->sql = $sql; }
        if (isset($id) && !isset($noload))
        {
            if (gettype($id) == "array") {
                $this->fill_from_row($id);
            } else {
                $this->fill($id);
            }
        } else {
            $this->name = $id;
        }
    }
    
    public function newClass()
    {
        $mod_name = "mod_".$this->loadName;
        return new $mod_name(($this->name));
    }
    
    public function loadModule()
    {
        if(modules::loadModule($this->loadName))
        {
            $this->loaded=true;
            return true;
        }
        return false;
    }
    
    public function reload()
    {
        $this->fill($this->name);
    }
    
    public function fill($name)
    {
        if (empty($this->sql)) { $this->sql = new sql(); }
        $res = $this->sql->quickExec1Line("select * from `tblModules` where `modName` = '".$name."'");
        if (isset($res))
        {
            $this->fill_from_row($res);
            return true;
        } else {
            return null;
        }
        
    }
    
    protected function fill_from_row($res)
    {
        $this->id = $res["id"];
        $this->name = $res["modName"];
        $this->priority = $res["modPriority"];
        $this->type = explode(",", $res["features"]);
        $this->version = $res["modVersion"];
        $this->desc = $res["modDesc"];
        $this->updateDate = $res["updateDate"];
        $this->installDate = $res["installDate"];
        $this->loadName = $res["isAliasFor"];
        if (empty($this->loadName)) { $this->loadName = $this->name; }
        $this->loaded = class_exists("mod_".$this->loadName);
    }
    
    public function updateVersion(){
        $mod = $this->newClass();
        if ($mod) {
            if (empty($this->sql)) { $this->sql = new sql(); }
            $this->sql->quickExec("update `tblModules` set `modVersion` = '".$mod::$version."' where `id` = $this->id");
            return true;
        } else {
            return false;
        }
    }
}


?>