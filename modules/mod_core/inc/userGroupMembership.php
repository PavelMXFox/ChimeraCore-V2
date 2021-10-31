<?php namespace fox;

class userGroupMembership extends baseClass {
    protected $id;
    protected $groupId;
    protected $userId;
    protected user $__user;
    protected userGroup $__group;
    
    public static $sqlTable="tblUserGroupLink";
    
    public static function getUsersInGroup(?userGroup $group=null, ?sql $sql=null) {
        
        if (empty($sql)) {
            $sql = new sql();
        }
        
        
        
        $res = $sql->quickExec("select * from `".self::$sqlTable."`".($group===null?"":" where `groupId` = '".$group->id."'"));
        $rv=[];
        while  ($row = mysqli_fetch_assoc($res)) {
            $item =  new self($row);
            if ($item->user!==null) {
                $rv[] = $item;
            }
            
        }
        
        return $rv;
    }
    
    public function loadUser() {
        if (empty($this->__user) && !empty($this->userId)) {
            try {
                $this->__user = new user($this->userId);
            } catch (\Exception $e) {
                return null;
            }
        }
        return $this->__user;
    }

    public function loadGroup() {
        if (empty($this->__group) && !empty($this->groupId)) {
            $this->__group = new userGroup($this->groupId);
        }
        return $this->__group;
    }
    
    
    public static function getGroupsForUser(?user $user=null, ?sql $sql=null ) {
        // if user===null -> get all items, else - get only items for user

        
        if (empty($sql)) {
            $sql = new sql();
        }
        
        
        
        $res = $sql->quickExec("select * from `".self::$sqlTable."`".($user===null?"":" where `userId` = '".$user->id."'"));
        $rv=[];
        while  ($row = mysqli_fetch_assoc($res)) {
            $item =  new self($row);
            if ($item->group !== null) {
                $rv[] = $item;
            }
        }

        return $rv;
        
    }
    
    public function __get($key) {
        switch ($key) {
            case "user":
                return $this->loadUser();

            case "group":
                return $this->loadGroup();
                
            default:
                return $this->__getDef($key);
        }
        
        
    }
    
    public function export() {
        $rv = parent::export();
        if (!empty($this->__user)) { $rv["user"] = $this->__user; }
        if (!empty($this->__group)) { $rv["group"] = $this->__group; }
        return $rv;
    }
}
?>