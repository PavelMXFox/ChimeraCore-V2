<?php
namespace fox;
require_once 'coreAPI.php';

class logger {
    protected sql $sql;
    public const sevError=0;
    public const sevAlert=1;
    public const sevWarning=2;
    public const sevInfo=3;
    public const sevDebug=4;
    
    public const severityNames =[
        0=>"ERROR",
        1=>"ALERT",
        2=>"WARNING",
        3=>"INFO",
        4=>"DEBUG",
    ];
    
    public function __construct(&$sql=null)
    {
        if (!empty($sql)) {
            $this->sql = $sql;
        } else {
            $this->sql = new sql();
        }
    }
    
    
    public function addEvent(string $source, string $data, int $severity=2, string $subjClass=null, int $subjId=null, array $addFields=null, $userId=null ) {
        global $session;
        
        $this->sql->prepareInsert("tblLogs");
        $this->sql->paramAddInsert("userId",(isset($userId)?$userId:$session->userId));
        $this->sql->paramAddInsert("source",$source);
        $this->sql->paramAddInsert('message',$data);
        $this->sql->paramAddInsert("severity",$severity);
        if (isset($subjClass)) { $this->sql->paramAddInsert("subjClass",$subjClass);}
        if (isset($subjId)) { $this->sql->paramAddInsert("subjId",$subjId);}
        if (isset($addFields)) { $this->sql->paramAddInsert("addFields",json_encode($addFields));}
        $this->sql->paramClose();
        $this->sql->execute();
        return true;
    }
    
    public static function addEventStatic(string $source, string $data, int $severity=2, string $subjClass=null, int $subjId=null, array $addFields=null, $userId=null) {
        $logger = new self();
        $logger->addEvent($source, $data, $severity, $subjClass, $subjId, $addFields, $userId);
    }
    
}