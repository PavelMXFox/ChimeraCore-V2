<?php


require_once($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/fox_api.php");

class mod_auth_internal extends baseModule_Auth
{
	public static $title="Internal auth module";
	public static $description="Модуль встроенной авторизации";
	public static $version="2.0.0";
	

	protected $sql;
	
	function __construct($instance=null, &$user=null, &$sql=null)
   {
   	if (isset($sql))
   	{
		 	$this->sql = $sql;  	
   	}
   	
   	if (isset($user))
   	{
			$this->user = $user;   	
   	}
   	parent::__construct($instance);
   }
   	
	public function install()
	{
		print "Local install of module ".static::$title."(".static::$version.")\n";
		parent::install();
	}
	
	protected function getSecret($password) {
	    return md5($password);
	}
	
	public function doAuth($authId, $authSecret=null, $authMethod=null, &$sql=null)
	{
		if (isset($sql)) {$this->sql = $sql; }
		if (empty($this->sql)) { $this->sql = new coreSql(); }
		if (empty($authSecret)) {return false; }		
		
		$password = $this->getSecret($authSecret);
    	$username = preg_replace('![^0-9A-Za-z@\._]+!', '', $authId);
    
    	if ($username == '' || $password == $this->getSecret('')) {
      	return false;
	   };
	   
	   $sqlQueryString = "select `id` as `userId` from `tblUsers` where  `login` = '$username' and `secret` = '$password' and `active`=1 and `deleted` =0 limit 1";
    
	   $result = $this->sql->quickExec($sqlQueryString);            
    	if (!$result) {
			return false;
	   }
                      
    	if (mysqli_num_rows($result) == 0) {
			return false;
	   };

    	$userId = mysqli_fetch_assoc($result)["userId"];
    	if ($this->user = new fox\user($userId,$sql))
    	{
    	    return $this->user->isRegistered;
    	} else {return false;}
    }	
	
	public function checkAuth(&$user=null)
	{
	    
		if (isset($user)) {$this->user = $user; }
		if (get_class($this) != "mod_auth_".$user->authType) { throw new Exception("Not my user! ".get_class($this)." != "."mod_auth_".$user->authType); }
		if ($this->user->isDeleted == false && $this->user->isActive == true) { return true; }

		return false;	
	}
	
	public function preCheckChangePassword(&$user) {
	    return true;
	}
	
	public function changePassword(&$user, $password, $sql=null) {
	    global $coreAPI;
	    if (isset($sql)) {$this->sql = $sql; }
	    if (empty($this->sql)) {
	        $this->sql = $coreAPI->SQL;
	    }
	    if (isset($user)) {$this->user = $user; }
	    if (get_class($this) != "mod_auth_".$this->user->authType) { throw new Exception("Not my user! ".get_class($this)." != "."mod_auth_".$this->user->authType); }
	    
	    $secret = $this->getSecret($password);
	    
	    $this->sql->quickExec("update `tblUsers` set `secret` = '".$secret."' where `id` ='".$this->user->id."'");
	    return true;
	}
	
	public function preCheckRegister($login, $eMail) { 
	    if ($u=$this->user->recoverSearch($login, $eMail, preg_replace("/^auth\_/", "", $this->instance),null, $this->sql)) {
	        if ($u->isRegistered) {
	           return false;
	        } 
	    }
	    return true;
	    
	}

	public function register(fox\user &$user) {
	    $user->authType=preg_replace("/^auth\_/", "", $this->instance);
	    if (!$this->preCheckRegister($user->login, $user->eMail)) {
	        return false;
	    }
	    
	    $user->isRegistered=true;
	    $user->save();
	    
	    return true;
	}
}


?>