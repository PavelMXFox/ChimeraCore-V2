<?php
namespace fox;
require_once("companyType.php");

class company {
    var $id;
    var $name;
    var $qName;
    var $description;
    
    protected $typeId;
    protected ?companyType $type=null;
    protected ?sql $sql=null;

    
    protected function checkSql() {
        if (empty($this->sql)) {$this->sql = new sql();}
    }
    
    function getType()
    {
        if (empty($this->type) && isset($this->typeId)) {
            $this->type = new companyType($this->typeId);
        }
        return $this->type;
    }
    
    function __construct($id=null, $noload = null, &$sql=null)
    {
        if (isset($sql)) {$this->sql = &$sql;}
        if (isset($id) && !isset($noload))
        {
            if (gettype($id) == "array") {
                $this->fill_from_row($id);
            } else {
                $this->fill($id);
            }
        } else {
            $this->id = $id;
        }
    }
    
    function reload()
    {
        return $this->fill($this->id);
    }
    
    function fill($id)
    {
        $this->checkSql();
        $res = $this->sql->sqlQuickExec1Line("select * from `tblCompany` where `id` = '".$id."'");;
        if (isset($res))
        {
            $this->fill_from_row($res);
            return true;
        } else {
            return null;
        }
    }
    
    function fill_from_row($res)
    {
        $this->id = $res["id"];
        $this->name = $res["fullName"];
        $this->qName = $res["sName"];
        if (!empty($res["qName"])) {
            $this->qName=$res["qName"];
        }
        
        $this->description = $res["description"];
        $this->typeId = $res["type"];
    }
}


?>