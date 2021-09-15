<?php
namespace fox;
require_once("coreAPI.php");

class session_Web extends session
{	
    protected function getClientIP()
    {
       return urlParcer::getClientIP();
    }
    
    public function doAuth($authId, $authSecret=null, $authMethod=null,&$sql=null) {
        $this->ipCurrent=$this->getClientIP();
        return parent::doAuth($authId,$authSecret,$authMethod,$sql);
    }
    
	public function check()
	{
	    $this->ipCurrent=$this->getClientIP();
		if (array_key_exists("mxsauthvt",$_COOKIE))
		{
			$this->sid=$_COOKIE["mxsauthvt"];
			if (parent::check() && ($this->type=="web")) {
				return true;				
			} else {
				$this->close();
			}	
		}
		return false;
	}
				
	public function open($userId)
	{
		$this->ipStart=$this->getClientIP();
		$this->ipCurrent=$this->getClientIP();
		$this->userAgent=$_SERVER["HTTP_USER_AGENT"];
		$this->type="web";
		
		if (parent::open($userId))
		{
			setcookie("mxsauthvt",$this->sid,time()+config::get("webSessionTimeout"));
			$this->cookieName = "mxsauthvt";
			$this->cookieTimeout=config::get("webSessionTimeout");
			$this->cookieVal=$this->sid;
			return true;
		}
		return false;
	}
	
	public function close()
	{
		if (!parent::close()) { return false; }
		setcookie("mxsauthvt",null,time()-1);
		return true;
	}
}

?>