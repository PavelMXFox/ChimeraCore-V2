<?php
namespace fox;

/**
 * 
 * Class fox/mailAccount
 * @property-read mixed $id
 * @property mixed $address
 * @property mixed $rxServer
 * @property mixed $rxProto
 * @property mixed $rxSSL
 * @property mixed $rxPort
 * @property mixed $rxFolder
 * @property mixed $rxArchiveFolder     #folder, where moved processed messages. If empty = no move, if 'trash' - message will be deleted
 * @property mixed $txServer
 * @property mixed $txProto
 * @property mixed $txSSL
 * @property mixed $txPort
 * @property mixed $login
 * @property mixed $password
 * @property mixed $rxLogin
 * @property mixed $rxPassword
 * @property mixed $rxLogin
 * @property mixed $rxPassword
 * 
 **/

class mailAccount extends baseClass {
    protected $id;
    protected $address;
    protected $rxServer;
    protected $rxProto='imap';
    protected $rxSSL=true;
    protected $rxPort=993;
    protected $txServer;
    protected $txProto='smtp';
    protected $txSSL=true;
    protected $txPort=465;
    protected $login;
    protected $password;
    
    protected $module;
    protected $rxFolder;
    protected $rxArchiveFolder;
    protected ?bool $default=false;
    
    
    public static $sqlTable = 'tblMailAccounts';
    
    public function __get($key) {
        switch ($key) {
            case "id": return $this->id;
            case "address": return $this->address;
            case "rxServer": return $this->rxServer;
            case "rxProto": return $this->rxProto;
            case "rxSSL": return $this->rxSSL;
            case "rxPort": return $this->rxPort;
            case "rxFolder": return $this->rxFolder;
            case "rxArchiveFolder": return $this->rxArchiveFolder;
            case "txServer": return $this->txServer;
            case "txProto": return $this->txProto;
            case "txSSL": return $this->txSSL;
            case "txPort": return $this->txPort;
            case "login": return $this->login;
            case "module": return $this->module;
            case "password": return xcrypt::decrypt($this->password);
            case "rxLogin": return $this->login;
            case "rxPassword": return xcrypt::decrypt($this->password);
            case "rxLogin": return $this->login;
            case "rxPassword": return xcrypt::decrypt($this->password);
            default: return parent::__getDef($key);
        }
    }
    
    public function __set($key, $val) {
        switch ($key) {
            case "address": $this->localSet($key, $val); break;
            case "rxServer": $this->localSet($key, $val); break;
            case "rxProto": $this->localSet($key, $val); break;
            case "rxSSL": $this->localSet($key, $val); break;
            case "rxPort": $this->localSet($key, $val); break;
            case "txServer": $this->localSet($key, $val); break;
            case "txProto": $this->localSet($key, $val); break;
            case "txSSL": $this->localSet($key, $val); break;
            case "txPort": $this->localSet($key, $val); break;
            case "login": $this->localSet($key, $val); break;
            case "password": $this->password = xcrypt::encrypt($val); $this->changelogAdd("password","old","new"); break;
            default: parent::__set($key, $val);
        }
    }
    
    public function connect() {
        return new mailClient($this);
    }
    
    protected function validateSave() {
        if (empty($this->login) || empty($this->password) || empty($this->address)) { return false;}
        
        return true;
    }
  
    protected function addParams() {
        $this->sql->paramAdd("address", $this->address);
        $this->sql->paramAdd("rxServer", $this->rxServer);
        $this->sql->paramAdd("rxProto", $this->rxProto);
        $this->sql->paramAdd("rxPort", $this->rxPort);
        $this->sql->paramAdd("txServer", $this->txServer);
        $this->sql->paramAdd("txProto", $this->txProto);
        $this->sql->paramAdd("txPort", $this->txPort);
        $this->sql->paramAdd("rxSSL", $this->rxSSL?"1":"0");
        $this->sql->paramAdd("txSSL", $this->txSSL?"1":"0");
        $this->sql->paramAdd("login", $this->login);
        $this->sql->paramAdd("password", $this->password);
        return true;
    }
       
    public static function getDefaultAccount(&$sql=null) {
        if (empty($sql)) { $sql = new \coreSql(); }
        $rv = $sql->quickExec1Line("select * from `tblMailAccounts` where `default` = 1 limit 1");
        if ($rv) {
            return new mailAccount($rv);
        } else {return null;}
    }

    
    public static function search($pattern,$module=null, $limit=10, &$sql=null) {
        
        /*
         * Ищет группу по шаблону (в полях address).
         *
         * Если module отсутствует - то считается, что module ='all' (игнорируется)
         *
         */
        
        $ruleWhere = '';
        
        if ($module !== null) {
            $ruleWhere = " AND `module` = '$module'";
        }
        
        
        if (empty($sql)) {$sql = new sql();}
        $sqlQueryString="SELECT `gr`.* FROM `".static::$sqlTable."`  as `gr`
        where
 `gr`.`address` like '%$pattern%'
        $ruleWhere
        group by `gr`.`id` limit $limit";
        
        $rv = [];
        
        
        $res = $sql->quickExec($sqlQueryString);
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($rv, new self($row));
        }
        
        return $rv;
    }
    
}