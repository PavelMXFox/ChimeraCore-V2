<?php
use fox\config;

require_once($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/fox_api.php");

class mod_auth_ldap extends baseModule_Auth
{
	public static $title="LDAP auth module";
	public static $description="Модуль локальной LDAP авторизации";
	public static $version="2.0.0";
	
	protected $sql;
	
	function __construct($instance=null,&$user=null, &$sql=null)
   {
   	parent::__construct($instance);

   	if (isset($sql))
   	{
		 	$this->sql = $sql;  	
   	}
   	
   	if (isset($user))
   	{
			$this->user = $user;   	
   	}
   	
   }
   	
	
	public function install()
	{
		print "Local install of module ".static::$title."(".static::$version.")\n";
		parent::install();
	}
	
	public function doAuth($authId, $authSecret=null, $authMethod=null, &$sql=null)
	{
		
		if (isset($sql)) {$this->sql = $sql; }
		if (empty($this->sql)) { $this->sql = new coreSql(); }
		if (empty($authSecret)) {return false; }		
		
		$res = $this->ldap_auth($authId, $authSecret);


		if (gettype($res) != "array")
		{
			return false;
		}

		$auth_username = $res[0]["userprincipalname"][0];
		$full_name = $res[0]["displayname"][0];

		$first_name = $res[0]["sn"][0];
		$last_name = $res[0]["givenname"][0];	
		$middle_name = $res[0]["middlename"][0];	

		$eMail = $res[0]["mail"][0];
		$auth_module = preg_replace("/^auth_/","",$this->instance);		
	
		if (config::get("singleCompanyMode")) {
		    $companyId=1;
		} else {
		    $companyId = config::get("companyId",$this->instance);
        }
        
		$this->user=fox\user::recoverSearch($auth_username,null,$auth_module, $this->sql);
		if (isset($this->user))
		{
		    $this->user->fullName = $full_name;
		    $this->user->eMail=$eMail;
		    $this->user->companyId=$companyId;
		    $this->user->save();
		    
		} else {
            $this->user = new fox\user(null,$this->sql);
            $this->user->authType = $auth_module;
            $this->user->login = $auth_username;
            $this->user->fullName=$full_name;
            $this->user->eMail=$eMail;
            $this->user->isRegistered=true;
            $this->user->companyId=$companyId;
            $this->user->save();
		}
	
		if ($this->user) 
		{ 
    			return true;
    	} else {return false;}
    }	
	
	public function checkAuth(&$user=null)
	{
		//var_dump($this);
		if (isset($user)) {$this->user = $user; }
		if ($this->user->isDeleted == false && $user->isActive == true) { return true; }

		return false;	
	}
	
	/// LDAP FUNCTIONS

	protected function ldap_auth($username, $password)
	{
		return $this->ldap_auth_main($username, $password);
	}

	protected function ldap_check($username)
	{
		return $this->ldap_auth_main($username, null, true);
	}

	protected function ldap_auth_main($username, $password=null, $checkonly=false)
	{

		$ldap_host = $this->settings["ldap_host"]; 
		$ldap_port = $this->settings["ldap_port"];
		$ldap_memberof = $this->settings["ldap_memberof"]; 
		$ldap_base = $this->settings["ldap_base"];
		$ldap_filter = $this->settings["ldap_filter"]; 
		$ldap_domain = $this->settings["ldap_domain"]; 
		$ldap_version = $this->settings["ldap_version"]; 
#		$ldap_session_expire = $this->settings["ldap_session_expire"]; 
		$ldap_check_username = $this->settings["ldap_check_username"]; 
		$ldap_check_password = $this->settings["ldap_check_password"];

		if (isset($username))
		{
			$ldap=ldap_connect($ldap_host,$ldap_port);
			ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, $ldap_version);
			if ($ldap)
			{
				if (isset($password))
				{
					$bind = ldap_bind($ldap,$username."@".$ldap_domain,$password);
				} elseif(isset($checkonly))
				{
					$bind = ldap_bind($ldap,$ldap_check_username."@".$ldap_domain,$ldap_check_password);
				} else {return -4; }
			
				if ($bind)
   	      {
					$result = ldap_search($ldap,$ldap_base,"(&(memberOf=".$ldap_memberof.")(".$ldap_filter.$username."))");
					$result_ent = ldap_get_entries($ldap,$result);
					ldap_unbind($ldap);
         	} else {
					return -1;
         	}
			} else {
				return -3;		
			}
			if ($result_ent['count'] != 0)
			{
				return $result_ent;
	      } else {
   	      return -2;
      	}
		} else {return false;}
	}
}


?>