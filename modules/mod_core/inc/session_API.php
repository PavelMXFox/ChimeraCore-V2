<?php
namespace fox;
require_once("coreAPI.php");

class session_API extends session
{	
    var ?\fox\APIMessage $message;
    
    protected function getClientIP()
    {
       return urlParcer::getClientIP();
    }
    
    public function doAuth($authId, $authSecret=null, $authMethod=null,&$sql=null) {
        $this->ipCurrent=$this->getClientIP();
        return false;
    }
    
    public function loadMessage(&$message) {
        $this->message = $message;
    }
    
	public function check()
	{
	    if (empty($this->message)) { return false; }
	    $this->auth = new baseModule_Auth("API");
	    $this->auth->user = $this->message->getUser();
	    $this->auth->userRights = auth::getUserRights($this->auth->user->id, $this->auth->user->sql);
	    return $this->checkAccess("API");
	    
	}
	
	
	public function open($userId)
	{
		return true;
	}
	
	public function close()
	{
        return true;
	}
}

?>