<?php
namespace fox;
class urlParcer
{
	public $module;
	public $function;
	public $parameters;

    public static function getClientIP()
    {
        if (array_key_exists("HTTP_X_REAL_IP", $_SERVER)) {$_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_X_REAL_IP"]; }
        elseif (array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER)) {$_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_X_FORWARDED_FOR"]; }
        return $_SERVER["REMOTE_ADDR"];
    }
	
	function __construct()
   {
    	$this->parce();
   }
	
	function parce() 
	{
		if (array_key_exists("FOX_REWRITE", $_SERVER) && $_SERVER["FOX_REWRITE"] != "yes")
		{
			$prefix = ($_SERVER["CONTEXT_PREFIX"]."index.php/");
		} else {
			$prefix = ($_SERVER["CONTEXT_PREFIX"]);
		}
   
   	$prefix = preg_replace(["![/]+!","![\.]+!"], ["\/","\."], $prefix);
   	$req=strtolower(preg_replace("/".$prefix."/", '', $_SERVER["REQUEST_URI"]));
		$req = explode("/",explode("?", $req, 2)[0]);

		if (count($req) > 0)
		{ 
			if($req[count($req)-1] == "") { array_splice($req,-1); }   
			if ($req[0] == "") { array_splice($req,0,1); }
		} 

		$this->module = ((count($req)>0)?$req[0]:NULL);
		$this->function = ((count($req)>1)?$req[1]:NULL);
   	
   	if (count($req) > 2)
   	{
	   	array_splice($req,0,-(count($req)-2));
   	} else {
			$req = [];   
   	}
   	$this->parameters=$req;
	}
    
	
	function shift() {
	    $this->module = $this->function;
	    $this->function = array_shift($this->parameters); 
	    
	}

}
?>