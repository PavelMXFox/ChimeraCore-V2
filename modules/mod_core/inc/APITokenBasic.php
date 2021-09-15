<?php 
namespace fox;

class APITokenBasic implements APITokenIface {
    protected $uuid;
    protected $__salt;
    
    function __construct($uuid, $salt) {
        $this->__salt=$salt;
        $this->uuid=$uuid;
    }
    
    public function sign($subject) {
        if (empty($this->uuid) || empty($this->__salt)) {
            throw new \Exception("Unable to sign by empty token");
        }
        return hash_hmac('sha256',json_encode($subject), $this->__salt);
    }
    
    public function validate($subject, $sign) {
        return $sign==$this->sign($subject);
    }
    
    public function getUUID() {
        return $this->uuid;
    }
    
}