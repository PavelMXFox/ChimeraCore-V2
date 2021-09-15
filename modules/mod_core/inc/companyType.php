<?php
namespace fox;
class companyType {
    var $id;
    var $name;
    
    function __construct($id, $noload=null, &$sql=null)
    {
        if (array_key_exists($id, self::compTypes)) {
            $this->id = $id;
            $this->name=self::compTypes[$id];
        } 
    }
    const compTypes=[1=>"Компания", "2"=>"Клиент"];
}