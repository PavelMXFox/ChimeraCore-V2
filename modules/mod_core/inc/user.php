<?php
namespace fox;
require_once "coreAPI.php";

/**
 * 
 * 
 * Class fox\user
 * @copyright MX STAR LLC 2020
 * @version 3.0.0
 * @author Pavel Dmitriev
 * @desc User class
 *
 * @property-read mixed $id
 * @property mixed $login
 * @property-read mixed $invCode
 * @property mixed $authType
 * @property mixed $authRefId
 * @property-read mixed $changelog
 * @property-read mixed $offlineAuthCtr
 * @property mixed $fullName
 * @property boolean $isActive
 * @property boolean $isDeleted
 * @property mixed $uiTheme
 * @property mixed $companyId
 * @property mixed $eMail
 * @property boolean $isRegistered
 * @property boolean $isConfirmed
 *
 *
 **/

class user extends baseClass
{
	protected $id;
	protected $login;
	protected $invCode;
	protected $authType;
	protected $authRefId;
	protected $offlineAuthCtr;
	protected $fullName;
	protected $isActive=true;
	protected $isDeleted=false;
	protected $uiTheme;
	protected $companyId;
	protected $eMail;
	protected $isRegistered=false;
	
	public static $sqlTable="tblUsers";
	protected static $excludeProps=['sql','changelog','__sqlSelectTemplate','fillPrefix','authRefId','company','offlineAuthCtr'];
	
	var $settings=["pagesize"=>30];
	
	protected ?company $company=null;

	public function __get($key) {
	    return parent::__getDef($key);
	}
	
	public function __set($key,$val) {
        parent::__setDef($key, $val);
	}
	
	public static function search($pattern, $companyId=null, $accessRule=null, $limit=10, &$sql=null) {
	    
	    /* 
	     * Ищет пользователей по шаблону (в полях логин и fullName).
	     * Если указана компания - то только в пределах компании
	     * Если указано accessRule - то только тех, которые входят в группы, на которых есть это правило.
	     * !!! Внимание !!! в отличие от проверки прав доступа - здесь поиск по isRoot не осуществляется!
	     * accessRule передается в формате access_rule_name@module.
	     *
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
    	    $ruleJoin = "left join `tblUserGroupLink` as `gl` on `gl`.`userId` = `u`.`id`
    	    left join `tblGroupRightsLink` as `gr` on `gr`.`groupId` = `gl`.`groupId`";
    	    $ruleWhere="and `gr`.`rule` = '$rule' and (`gr`.`module` = '$module' or `gr`.`module` = 'all')";
	    }
	    
	    if (empty($sql)) {$sql = new sql();}
	    $sqlQueryString="SELECT `u`.* FROM `tblUsers`  as `u`
        $ruleJoin
        where (`u`.`companyId` is NULL OR `u`.`companyId` = '$companyId')
            AND `u`.`active` = 1
            AND `u`.`deleted` = 0
            AND (`u`.`invCode` = '".UID::clear($pattern)."'
	        OR `u`.`login` like '%$pattern%'
	        OR `u`.`fullName` like '%$pattern%')
        $ruleWhere
        group by `u`.`id` limit $limit"; 
	    
        $rv = [];
        $res = $sql->quickExec($sqlQueryString);
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($rv, new self($row));
        }
        
        return $rv;
	}
	
	public static function recoverSearch($login=null, $email=null, $authType=null,$sql=null) {
	    if (empty($sql)) { $sql = new sql(); }
	    
	    $where="";
	    if (!empty($login)) {
	        if (!empty($where)) { $where .= " and "; }
	        $where .= " `login` = '".$login."'";
	    }

	    if (!empty($email)) {
	        if (!empty($where)) { $where .= " and "; }
	        $where .= " `eMail` = '".$email."'";
	    }
	    
	    if (!empty($authType)) {
	        if (!empty($where)) { $where .= " and "; }
	        $where .= " `authType` = '".$authType."'";
	    }
	    
	    $res = $sql->quickExec1Line("select * from `tblUsers` where ".$where." limit 1");
	    if (!$res) { return null; }
	    return new self($res);
	}

	public static function getUserByEmail($email, $create=true, &$sql=null) {
	    if ($u = self::recoverSearch(null, $email,null,$sql)) {
	        return $u;
	    } else if ($create) {
	        $u = new user();
	        $u->eMail=$email;
	        $u->isRegistered=false;
	        $u->save();
	        return $u;
	    } else {
	        return null;
	    }
	    
	}
	
	public function getCompany():?company {
	    if (empty($this->company) and isset($this->companyId)) {
	        $this->company=new company($this->companyId,$this->sql);
	    }
	    return $this->company;
	}
	
	public function getUID() {
	    return UID::print($this->invCode);
	}
	
    public function __construct($id=null, $sql = null, &$sql2=null) {
        if ((gettype($sql2)=='object') && gettype($sql) != 'object') {
            $caller = debug_backtrace(); $caller = next($caller);
            trigger_error('Deprecated call to fox\user::__construct($id, $noload, $sql) from function '.$caller['function'].' at '.$caller['file'].' on line '.$caller['line'], E_USER_WARNING);
            $sql = $sql2;
        };
    	parent::__construct($id,$sql);
    }
	
	public function reload()
	{
		return $this->fill($this->id);	
	}
	
	
	protected function fillFromRow($res)
	{
		$this->id = $res["id"];
		$this->login = $res["login"];
		$this->invCode = $res["invCode"];
		$this->authType = $res["authType"];
		$this->authRefId = $res["authRefId"];
		$this->offlineAuthCtr = $res["offlineAuthCtr"];
		$this->fullName = $res["fullName"];
		$this->isActive = ($res["active"] == 1);
		$this->isDeleted = ($res["deleted"] == 1);
		$this->uiTheme=$res["uiTheme"];
		$this->companyId=$res["companyId"];
		$this->eMail=$res["eMail"];
		$this->isRegistered=$res["registered"]==1;
	}
	
	protected function create() {
	    if (empty($this->authType)) {
	        $this->authType="none";
	    }

	    if (empty($this->login)) {
	        if (!empty($this->eMail) && net::validateEMail($this->eMail)) {
	            $this->login = $this->eMail;
	        } else {
	            throw new \Exception("Invalid email ".$this->eMail);
	        }
	    }

	    $us2=user::recoverSearch($this->login,$this->eMail,$this->authType,$this->sql);
	    if ($us2 && $us2->isRegistered && !$us2->isDeleted) {
	        throw new \Exception("User ".$this->login." already exists");
	    }
	    
	    if ($u2 = user::recoverSearch(null, $this->eMail,null,$this->sql)) {
	        if ($u2->isRegistered) {
	            return false;
	        }

	        if (!$this->isRegistered) {
	            return true;
	        }
	        
	        $this->id=$u2->id;
	        return parent::update();
	        
	    }
	  
	    
	    if (empty($this->fullName)) {
	        $this->fullName = $this->login;
	        $this->isRegistered=false;
	    };
	    
	    $this->invCode = UID::issue("core", "user",null,$this->sql);
	    
	    if (parent::create()) {
	       UID::link($this->invCode,$this->id, $this->sql);
	       return true;
	    }
	    return false;
	    
	}
	
	protected function createAddParams() {
	    $this->sql->paramAdd("invCode", $this->invCode);
	    
	    return $this->addParams();
	}

	protected function addParams() {
	    $this->sql->paramAdd("login", $this->login);
	    $this->sql->paramAdd("authType", $this->authType);
	    $this->sql->paramAdd("authRefId", $this->authRefId,null,empty($this->authRefId));
	    $this->sql->paramAdd("fullName", $this->fullName);
	    $this->sql->paramAdd("active", $this->isActive?1:0);
	    $this->sql->paramAdd("deleted", $this->isDeleted?1:0);
	    $this->sql->paramAdd("uiTheme", $this->uiTheme,null,empty($this->uiTheme));
	    $this->sql->paramAdd("companyId", $this->companyId,null,empty($this->companyId));
	    $this->sql->paramAdd("eMail", $this->eMail,null,empty($this->eMail));
	    $this->sql->paramAdd("registered", $this->isRegistered?1:0);
	    return true;
	}
	
	public static function invite($email=null, $groups=[], $validDays=30) {

	    $at = new authTicket();
	    $at->payload = [
	        "operation"=>"userInvite",
	        "eMail"=>$email,
	        "eMailRestrict"=>!empty($email),
	        "groups"=>$groups
	    ];
	    $at->expireDays($validDays);
	    $at->save();
	    
	    if (empty($email)) {
	        return $at;
	    }
	    
	    $html="<div id='preheader' style='display:none;'>Приглашение пользователя на ".config::get("svcName")."</div>
<div  style='font-family: sans-serif; color: #02394E'>
Добрый день!<br/>
Вас пригласили для регистрации на сайте <a style='color:  #ff6f0f; text-decoration: underline;' href='".config::get("sitePrefix")."'>".config::get("svcName")."</a><br/>
<br/>
    
Для регистрации регистрации укажите код <span style='color: #ff6f0f'>".$at->uidPrint."</span> в форме регистрации по адресу <a style='color:  #ff6f0f; text-decoration: underline;' href='
".config::get("sitePrefix")."/login/register'>".config::get("sitePrefix")."/login/register</a><br/>
или перейдите по <a style='color:  #ff6f0f; text-decoration: underline;' href='
".config::get("sitePrefix")."/login/register?code=".$at->uid."'>ссылке</a><br/>
<br/>
Если регистрация не требуется либо письмо было отправлено по ошибке - просто проигнориуйте его.
<br/>
    
Отвечать на это письмо не нужно, так как оно отправлено автоматически.
<br/>
<br/>
С уважением,<br/>
Команда <span style='color: #ff6f0f'>".config::get("svcName")."</span>
    
</div>
";
	    $msg = new mailMessage();
	    $msg->addRecipient($email);
	    $msg->subject="Приглашение на регистрацию";
	    $msg->bodyHTML=$html;
	    $msg->direction="TX";
	    $msg->fromRobot=true;
	    $msg->account=config::get("infoEMailAccount");
	    $msg->mailFrom=config::get("infoEMail");
	    $msg->send();
	    
	    return $at;
	}
    
	public function export() {
	    $rv = parent::export();
	    $rv["invCodePrint"] = UID::print($this->invCode);
	    return $rv;
	}

}

?>