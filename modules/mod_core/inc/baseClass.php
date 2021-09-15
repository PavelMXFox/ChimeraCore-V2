<?php
namespace fox;

/**
 * Class fox\baseClass
 * @copyright MX STAR LLC 2020
 * @version 3.0.0
 * @author Pavel Dmitriev
 * @desc MailMessage class
 *
 * @property-read mixed $changelog
 *
 *
 **/


class baseClass implements \JsonSerializable {
    
   
    protected ?\fox\sql $sql=null;
    protected $fillPrefix="";
    protected $changelog=null;
    protected $__settings;
    
    public static $sqlTable=null;
    public static $sqlSelectTemplate=null;
    public static $sqlIdx="`id`";
    protected $__sqlSelectTemplate=null;
    protected static $excludeProps=['sql','changelog','__sqlSelectTemplate','fillPrefix'];
    
    protected static $sqlColumns=[];
    
    public function getSqlColumnsList($tName="", $colPrefix="") {
        
        $rv="";
        foreach (static::$sqlColumns as $c) {

            $rv .= (($rv=="")?"":", ")."`".$tName."`.`".$c."` as `".$colPrefix.$c."`";
        }
        
        return $rv;
    }
    
    protected function checkSql() {
       
        global $coreAPI;
        if ($this->sql ===null) {
            $this->sql = $coreAPI->SQL;
        }
        return;
    }
    
    public function __construct($id=null, ?namespace\sql $sql=null, $prefix=null, $settings=null) {
        
        
        $this->__settings=$settings;
        if (empty($this::$sqlSelectTemplate) and !empty($this::$sqlTable)) {
            $this->__sqlSelectTemplate="select * from `".$this::$sqlTable."` as `i`";
        } else {
            $this->__sqlSelectTemplate=$this::$sqlSelectTemplate;
        }
        
        if (isset($sql)) { $this->sql = &$sql;}
        $this->fillPrefix = $prefix;
        
        switch (gettype($id)) {
            case "array":
                $this->fillFromRow($id);
                break;
            case "string":
                $this->fill($id);
                break;
            case "integer":
                $this->fill($id);
                break;
            case "NULL":
                break;
            default:
                throw new \Exception("Invalid type ".gettype($id)." for ".get_class($this)."->__construct",591);
                break;
        }
    }
    
    protected function fill($id) {
        if (!empty($this->__sqlSelectTemplate)) {
            $this->checkSql();
            $row = $this->sql->quickExec1Line($this->__sqlSelectTemplate." where `i`.".$this::$sqlIdx." = '".$id."'");
            if (!empty($row)) {
                $this->fillFromRow($row);
            } else {
                throw new \Exception("Record with id ".$id." not found in ".get_class($this),691);
            }
        } else {
            throw new \Exception("Fill by ID not implemented in ".get_class($this), 592);
        }
    }
    
    protected function fillFromRow($row) {
        foreach ($row as $key=>$val) {
            if (!empty($this->fillPrefix)) {
                if (!preg_match("/^".$this->fillPrefix."/", $key)) {
                    continue;
                }
                $key=preg_replace("/^".$this->fillPrefix."/", "", $key);
            }
            
            if (property_exists($this, $key) || property_exists($this, "__".$key)) {
                if (property_exists($this, "__".$key))
                {
                    $key = "__".$key;
                }
                
                if (gettype($this->{$key})=='boolean') {
                    $this->{$key} = $val==1;
                } else {
                    $this->{$key} = $val;
                }
            }
        }
    }
    
    public function save() {
        if (!$this->validateSave()) { return false; }
        $this->checkSql();

        if (empty($this->id)) {
            return $this->create();
        } else {
            if (empty($this->changelog)) { return true;}
            return $this->update();
        }
    }
    
    protected function update() {
        if (!empty($this::$sqlTable)) {
            $this->sql->prepareUpdate($this::$sqlTable);
        }
        
        if (empty($this::$sqlTable) || !$this->updateAddParams()) {
            throw new \Exception("Method update not implemented in ".get_class($this), 593);
        }
        $this->sql->paramClose($this::$sqlIdx." = '".$this->id."'");
        $this->sql->quickExecute();
        return false;
    }
    
    protected function create() {        
        

        if (!empty($this::$sqlTable)) {
            $this->sql->prepareInsert($this::$sqlTable);
        }
        
        
        if (empty($this::$sqlTable) || !$this->createAddParams()) {
            throw new \Exception("Method create not implemented in ".get_class($this), 594);
        }
        
        $this->sql->paramClose();
        $this->sql->quickExecute();
        if (property_exists($this, "id")) { 
            $this->id = $this->sql->getInsertId(); 
            $this->fill($this->id);
        }
        return true;
    }
    
    protected function updateAddParams() {
        return $this->addParams();
    }
    
    protected function createAddParams(){
        return $this->addParams();
    }
    
    protected function addParams() {
        return false;
    }
    
    protected function changelogAdd($varName, $oldVal, $newVal) {
        if ($oldVal==$newVal) { return; };
        if (empty($this->changelog)) { $this->changelog = "Изменены значения: "; }
        if (gettype($oldVal)=='array') {$oldVal='array';}
        if (gettype($newVal)=='array') {$newVal='array';}
        $this->changelog .= " '$varName' c '$oldVal' на '$newVal';";
    }

    protected function localSet($varName, $newVal) {
        $this->changelogAdd($varName, $this->{$varName}, $newVal);
        $this->{$varName} = $newVal;
    }
    
    protected function validateSave() {
        return true;
    }
    
    protected function __getDef($key) {
        try {
            return self::__get($key);
        } catch (\Exception $e) {
            if (!preg_match("!^_!",$key) && property_exists($this, $key)) {// && isset($this->{$key})) {
                return $this->{$key};
            } else {
                throw $e;
            }
        }
    }
    
    protected function __setDef($key, $val) {
        if ( !preg_match("!^_!",$key) && property_exists($this, $key)) {
            $this->localSet($key, $val);
        } else {
            self::__set($key,$val);
        }
    }
    public function __get($key) {
        switch ($key) {
            case "id":
                if (property_exists($this, "id")) { return $this->id;} else {return  null;};
                break;
            case "sqlSelectTemplate":             
                return $this->__sqlSelectTemplate;
                break;
            case "sql":
                   $this->checkSql();
                return $this->sql;
            case "changelog":
                return $this->changelog;
                break;
            default: throw new \Exception("property $key not availiable for read in class ".get_class($this), 595); break;
        }
    }
    
    public function getSql() {
        $this->checkSql();
        return $this->sql;
    }
    public function __set($key,$val) {
        switch ($key) {
            case "settings":
                $this->__settings=$val;
                break;
            default: throw new \Exception("property $key not availiable for write in class ".get_class($this), 596); break;
        }
    }
    
    public function __debugInfo() {
        
        $rv =[];
        foreach($this as $key => $value) {
            if (array_search($key, $this::$excludeProps) === false && !preg_match("!^_!",$key)) {
                $rv[$key]= $value;
            }
        }
        return $rv;
    }

    public function export() {
        $rv =[];
        foreach($this as $key => $value) {
            if (array_search($key, $this::$excludeProps) === false && !preg_match("!^_!",$key)) {
                $rv[$key]= $this->__get($key);
            }
        }
        return $rv;
    }
    
    public function jsonSerialize()
    {
        return $this->export();
    }
}