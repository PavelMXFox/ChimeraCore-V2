<?php 
namespace fox;

class legalDoc extends baseClass {
    protected $id;
    protected $__fileId;
    protected ?file $__file=null;
    protected $href;
    protected bool $ackRequired=false;
    protected $title;
    protected bool $active=true;
    protected bool $deleted=false;
    
    public ?bool $ack=null;
    
    public static $sqlTable="tblDocuments";
    public static $sqlTableAck="tblDocumentsAck";
    
    public function __get($key) {
        switch ($key) {
            default:
                return $this->__getDef($key);
        }
    }
    /*
     * $user - get documents for user with ack status, if null = return without ack-flags
     * 
     * 
     */
    public static function getDocuments(user $user=null, $showDeleted=false) {
        $sql = new \coreSql();
        if ($user) {
            $res=$sql->quickExec("select `d`.*, `a`.`ackStatus` as `ackStatus` from `".static::$sqlTable."` as `d` left join `".static::$sqlTableAck."` as `a` on `a`.`docId` = `d`.`id` and `a`.`userId` = '".$user->id."'" .($showDeleted?"":" where `deleted` = 0"));
        } else {
            $res=$sql->quickExec("select `d`.*, NULL as `ackStatus` from `".static::$sqlTable."` as `d`".($showDeleted?"":" where `deleted` = 0"));
        }

        $rv=[];
        while ($row = mysqli_fetch_assoc($res)) {
            $doc = new self($row);
            if ($user) {
                $doc->ack=$row["ackStatus"]==1;
            } else {
                $doc->ack=null;
            }
            $rv[]=$doc;
        }

        return $rv;
    }
    
    public static function getNACKDocs(user $user) {
        $d = static::getDocuments($user);
        $rv=[];
        foreach ($d as $doc) {
            if ($doc->ackRequired && !$doc->ack) {
                $rv[] = $doc;
            }
        }
        return $rv;
    }

    protected function getACK(user $user) {
        if ($this->id===null) {
            throw new \Exception("Unable ack empty doc");
        }
        
        $this->checkSql();
        $res = $this->sql->quickExec1Line("select `ackStatus` from `".static::$sqlTableAck."` where `userId` = '".$user->id."' and `docId` = '".$this->id."'");
        if ($res) {
            $this->ack=$res["ackStatus"];
            return $res["id"];
        } else {
            $this->ack=null;
            return null;
        }
    }
    
    public function ACK(user $user) {
        if ($this->id===null) {
            throw new \Exception("Unable ack empty doc");
        }
        
        $aid = $this->getACK($user);
        if (empty($aid)) {
            // insert
            $this->sql->quickExec("insert into `".static::$sqlTableAck."` (`docId`, `userId`,`ackStatus`) values ('".$this->id."','".$user->id."','1')");
        } else {
            // update
            $this->sql->quickExec("update `".static::$sqlTableAck."` set `ackStatus` = 1 where `docId` = '".$this->id."' and `userId` ='".$user->id."'");
        }
        $this->ack=true;
    }
    
    public static function qACK(user $user, $id) {
        $doc = new self($id);
        $doc->ACK($user);
        return true;
    }
    
}

?>