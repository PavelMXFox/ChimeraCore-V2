<?php

namespace fox;

class authTicket extends baseClass {
    protected $uid;
    protected $__payload;
    protected $payload;
    protected $dateEntry;
    protected $dateExpire;
    
    public static $sqlTable='tblAuthTickets';
    public static $sqlIdx="`uid`";
    
    protected function fillFromRow($row) {
        parent::fillFromRow($row);
        $this->payload=json_decode($this->__payload);
    }

    public function __construct($id=null, ?sql $sql=null, $prefix=null ) {
        self::clearExpired($sql);
        parent::__construct($id, $sql, $prefix);
    }
    
    public function expireSeconds($val) {
        $this->dateExpire=stamp2iso_date(time()+($val));
    }

    public function expireHours($val) {
        $this->dateExpire=stamp2iso_date(time()+(3600*$val));
    }

    public function expireDays($val) {
        $this->dateExpire=stamp2iso_date(time()+(3600*24*$val));
    }
    
    public function delete() {
        
        if (!empty($this->uid)) {
            $this->checkSql();
            
            $this->sql->quickExec("delete from `".$this::$sqlTable."` where ".$this::$sqlIdx." = '".$this->uid."'");
            trigger_error("delete from `".$this::$sqlTable."` where ".$this::$sqlIdx." = '".$this->uid."'", E_USER_WARNING);
            return true;
        } else {
            return false;
        }
    }
    
    protected function create() {
        
        $this->__payload=json_encode($this->payload);
        if (empty($this->dateExpire)) { $this->dateExpire=stamp2iso_date(time()+(3600*24));}
        
        for ($j=0; $j<5; $j++) {
            $this->uid=common::genPasswd(12, array('0','1','2','3','4','5','6','7','8','9'));
            try {
                parent::create();
                break;
            } catch (\Exception $e) {
                if ($j >=4) {
                    throw new \Exception("Unable to generate uniqId fot authTicket (".$e->getMessage().")");
                }
            }
        }
        
        $this->fill($this->uid);
    }
    
    public function __get($key) {
        switch ($key) {
            case "uidPrint":
                return substr($this->uid, 0, 4)."-".substr($this->uid, 4, 4)."-".substr($this->uid, 8, 4);
                break;
            default:
                return $this->__getDef($key);
        }
        
    }

    public function __set($key, $val) {
        return $this->__setDef($key, $val);
    }
    
    protected function createAddParams(){
        $this->sql->paramAdd("uid", $this->uid);
        return $this->addParams();
    }

    protected function addParams() {
        $this->sql->paramAdd("payload", $this->__payload);
        $this->sql->paramAdd("dateExpire", $this->dateExpire);
        return true;
    }
    
    public static function clearExpired(&$sql=null) {
        if (empty($sql)) {
            $sql = new sql();
        }
        $sql->quickExec("delete from `".self::$sqlTable."` where  `dateExpire` < NOW()");
    }
}