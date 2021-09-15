<?php 

namespace fox;
use Exception;

/**
 * Class foxsd\mailAddress
 * @property mixed $name
 * @property mixed $address
 * @property-read $full
 * 
 */

class mailAddress{
    protected $name;
    protected $address;
    
   
    public function __construct($name, $address=null){
        
        if (!empty($address)) {
            $this->__set("name", $name);
            $this->__set("address", $address);
        } else {
            $res=[];
            $name = preg_replace('!^[\"\ \t]*|[\"\ \t]*$!', '', $name);
            if(preg_match("/([^\<\>]*) \<([^\<\>]*)\>/", $name, $res)) {
                $this->address=$res[2];
                $this->name=$res[1];
            } elseif (preg_match("/\<([^\<\>]*)\>/", $name, $res)) {
                $this->name=$this->address=$res[1];
            } elseif(net::validateEMail($name)) {
                $this->name=$this->address=$name;
            } else {
                throw new \Exception("Invalid input value '$name'");
            }
        }
        
        $this->name = preg_replace('!^[\"]*|[\"]*$!', '', $this->name);
    }
    
    
    public function __get($key) {
        switch($key) {
            case "name":
                return $this->name;
            case "address":
                return $this->address;
            case "full":
                return ''.$this->name." <".$this->address.">";
            default: return parent::__get($key);
        }
    }
    
    public function __set($key,$val) {
        switch ($key) {
            case "name":
                $this->name = $val;
                break;
            case "address":
                if (net::validateEMail($val)) {
                    $this->address=$val;
                } else {
                    throw new Exception("Invalid EMail '$val'");
                }
                break;
            default: parent::__set($key,$val);
        }
    }
    
    public function __debugInfo() {
        
        return ["full"=>$this->__get("full")];
    }
}
?>