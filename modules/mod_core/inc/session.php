<?php 
namespace fox;

class session
{
	var $sid;
	var $userId;
	var $ipStart;
	var $ipCurrent;
	var $ipLast;
	var $userAgent;
	var $lastActivity;
	var $type;
	var $isActive;
	var ?baseModule_Auth $auth;
	
	const sessionUpdateInterval = 1800;  // Обновлять сессию каждые полчаса
	const sessionTimeout = 2592000;      // Сессия протухает через 30 дней неактивности
	
	protected $sql;
	
	function __construct(&$sql=null)
   {
   	if (isset($sql))
   	{
		 	$this->sql = $sql;  	
   	}
   }
   
   public function checkAccess($rule,$module="all")
   {
 		return auth::checkAccess($rule,$module, $this->auth->userRights);
   }
   
   public function doAuth($authId, $authSecret=null, $authMethod=null,&$sql=null)
   {
    global $coreAPI;
   	$this->auth = auth::doAuth($authId, $authSecret, $authMethod,$sql);

		if ($this->auth)
		{
			$this->userId = $this->auth->user->id;
			
			$retval = ($this->open($this->auth->user->id));
			$coreAPI->logger->addEvent("core_auth", "Auth success for user '".$this->auth->user->login."' via '".get_class($this)."' from '".$this->ipCurrent."'",logger::sevInfo,"authSuccess",inet_aton($this->ipCurrent),["sid"=>$this->sid,"ip"=>$this->ipCurrent],0);
			return $retval;
		} else {
		    $coreAPI->logger->addEvent("core_auth", "Auth failed for user '$authId' via '".get_class($this)."' from '".$this->ipCurrent."'",logger::sevWarning,"authFailed",inet_aton($this->ipCurrent),["ip"=>$this->ipCurrent],0);
		    return false; 
		}
   }
      

	   
	public function check()
	{
	    global $coreAPI;
		if (!$this->reload()) { 
		    $coreAPI->logger->addEvent("core_auth", "Session with SID: '".$this->sid."' not found.",logger::sevWarning,"ip",inet_aton($this->ipCurrent),["sid"=>$this->sid, "ip"=>$this->ipCurrent],0);
		    return false; 
		}
		if (!$this->isActive) { 
		    return false; 
		}		
		$this->auth = auth::checkAuth($this->userId,$this->sql);
	
		if ($this->auth) {
		    if (($this->ipLast != $this->ipCurrent) || (time() - iso_date2stamp($this->lastActivity) > $this::sessionUpdateInterval)) {
		        // update session data
		        $this->sql->quickExec("UPDATE `tblSessions` set `currentIp` = '".inet_aton($this->ipCurrent)."', `lastActivity`=NOW() where `id` = '".$this->sid."'");
		    }
		    
		    return true;
		}
		else {
		    return false;
		}
	}

	protected function load($sessionId)
	{
		$this->sid = $sessionId;
		$this->reload();	
	}
	
	protected function reload()
	{
		if (empty($this->sql)) {$this->sql=new sql(); }
		$res = $this->sql->quickExec1Line("SELECT `id`,`userId`,inet_ntoa(`startIP`) as `startIP`, inet_ntoa(`currentIP`) as `currentIP`, agent, lastActivity,type,isClosed FROM `tblSessions` WHERE `id` = '$this->sid'");
		if ($res)
		{
			$this->userId = $res["userId"];
			$this->ipStart = 	$res["startIP"];
			$this->ipLast = $res["currentIP"];
			$this->ipCurrent = $res["currentIP"];
			$this->userAgent = $res["agent"];
			$this->lastActivity = $res["lastActivity"];
			$this->type=$res["type"];
			$this->isActive = empty($res["isClosed"]);
			return true;
		}
		return false;
	}

	public function open($userId)
	{
		if (empty($this->sql)) {$this->sql=new sql(); }
		$sid = getGUIDc();


		for ($i=0; $i<=127; $i++)
		{
			$res = $this->sql->quickExec1Line("SELECT COUNT(`id`) as `c` FROM `tblSessions` WHERE `id` = '$sid'");
			if (!$res) { return false; }
			if ($res["c"] == 0) { break;}
		}
		

		if ($i >=127) { return false; }
		$this->sid = $sid;
		$this->userId = $userId;
		
		$this->sql->prepareInsert("tblSessions");
		$this->sql->paramAddInsert("id",$this->sid);
		$this->sql->paramAddInsert("startIP",inet_aton($this->ipStart));
		$this->sql->paramAddInsert("currentIP",inet_aton($this->ipCurrent));
		$this->sql->paramAddInsert("agent",$this->userAgent);
		$this->sql->paramAddInsert("userId",$this->userId);
		$this->sql->paramAddInsert("type",$this->type);
		
		$this->sql->paramClose();


		
	
		if ($this->sql->quickExecute())
		{
			$this->reload();
			return true;						
		}

		 
		return false;
	}
	
	public function close()
	{
		if($this->sql->quickExec("UPDATE `tblSessions` set `isClosed` = 1 where `id` = '".$this->sid."'"))
		{
			return true;		
		} else {return false; }
	}
}
