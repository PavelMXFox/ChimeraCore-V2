<?php namespace fox;

class userGroup extends baseClass {
    protected $id;
    protected $name;
    protected $companyId;
    protected $__company;
    protected bool $isList=false;
    protected $__acl=null;
    
    public static $sqlTable="tblUserGroups";
    public static $aclSqlTable="tblGroupRightsLink";
    
    public function getMembers() {
        $this->checkSql();
        $rv=[];
        foreach (userGroupMembership::getUsersInGroup($this, $this->sql) as $ugm) {
            $rv[$ugm->user->id]=$ugm->user;   
        }
        return $rv;
    }
    
    public static function getForUser(user $user, $isList=false, ?sql $sql=null ) {
        
        if (empty($sql)) { $sql = new \coreSql(); }
        $ugms=userGroupMembership::getGroupsForUser($user,$sql);
        
        $rv=[];
        
        foreach ($ugms as $ugm) {
            if ($isList===null || ($ugm->group->isList === $isList)) {
                $rv[] = $ugm->group;
            }
        }
        
        return $rv;
    }
    
    public function __get($key) {
        switch ($key) {
            case "company":
                if (empty($this->__company) && !empty($this->companyId)) {
                    $this->__company = new company($this->companyId);
                }
                return $this->__company;
            case "acl":
                if ($this->__acl===null) {
                    $this->loadACL();
                }
                return $this->__acl;
            default:
                return $this->__getDef($key);
        }
        
    }

    public static function search($pattern, $isList=false, $accessRule=null, $limit=10, &$sql=null) {
        
        /*
         * Ищет группу по шаблону (в полях name и fullName).
         * $isList { true, false, null (ignore/all) }
         * 
         * accessRule передается в формате access_rule_name@module.
         * !!! Внимание !!! в отличие от проверки прав доступа - здесь поиск по isRoot не осуществляется!
         * Если module отсутствует - то считается, что module ='all'
         *
         */
        
        $ruleWhere = '';
        $ruleJoin='';
        
        if ($accessRule) {
            $accessRule = explode('@', $accessRule);
            $rule = $accessRule[0];
            
            if (array_key_exists(1, $accessRule)) {
                $module = $accessRule[1];
            } else {
                $module = 'all';
            }
            $ruleJoin = "left join `tblGroupRightsLink` as `gx` on `gx`.`groupId` = `gr`.`groupId`";
            $ruleWhere=" and `gx`.`rule` = '$rule' and (`gx`.`module` = '$module' or `gx`.`module` = 'all')";
        }
        
        if ($isList !== false) {
            $ruleWhere .= " and `gr`.`isList` = ".($isList==true?1:0); 
        }
        
        if (empty($sql)) {$sql = new sql();}
        $sqlQueryString="SELECT `gr`.* FROM `".static::$sqlTable."`  as `gr`
        $ruleJoin
        where
 `gr`.`name` like '%$pattern%'
        $ruleWhere
        group by `gr`.`id` limit $limit";
        
        $rv = [];
        
        
        $res = $sql->quickExec($sqlQueryString);
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($rv, new self($row));
        }
        
        return $rv;
    }
    
    public function loadACL() {
        $this->checkSql();
        $res = $this->sql->quickExec('select * from `'.static::$aclSqlTable.'` where `groupId` = "'.common::clearInput($this->id,"0-9").'"');
        $rv=[];
        while ($row=mysqli_fetch_assoc($res)) {
            $rv[] = $row;
        }
        $this->__acl=$rv;
    }

}

?>