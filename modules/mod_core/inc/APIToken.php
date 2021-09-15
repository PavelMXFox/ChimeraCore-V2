<?php

namespace fox;

class APIToken extends baseClass implements APITokenIface {
    protected $id;
    protected $uuid;
    protected $__salt;
    protected $__userId;
    protected ?user $__user;
    protected $issueDate;
    protected $expireDate;
    
    public static $sqlTable='tblAPITokens';

    public function loadByUUID($uuid) {
        $this->checkSql();
        $uuid = strtoupper(preg_replace("/[^0-9a-zA-Z-]/", "", $uuid));
        $row = $this->sql->quickExec1Line($this->__sqlSelectTemplate." where `i`.`uuid` = '".$uuid."' and (expireDate is NULL OR expireDate > NOW())");
        if (empty($row)) { throw new \Exception("Token ".$uuid." not found or expired!"); }
        $this->fillFromRow($row);
    }
    
    public static function getByUUID($uuid, $sql=null) {
            $t = new self(null,$sql);
            $t->loadByUUID($uuid);
            return $t;
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
    
    public function __get($key) {
        switch ($key) {
            case "user":
                if (empty($this->__user) && !empty($this->__userId)) {
                    $this->__user=new user($this->__userId);
                }
                return $this->__user;
            
            default:
                return $this->__getDef($key);
        }
    }

    public function getUUID() {
        return $this->uuid;
    }
}
