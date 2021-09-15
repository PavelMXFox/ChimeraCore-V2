<?php


namespace fox;
use Exception;
require_once("coreAPI.php");

class modules
{
	var $modules;
	
	
	public function isLoaded($mod_name)
	{   
	    if ($this->modules === null) {
	        $this->loadModules();
	    }
	    
		return (array_key_exists($mod_name, $this->modules) && $this->modules[$mod_name]->loaded);
	}
	
	public function getClass($mod_name)
	{
		if ($this->isLoaded($mod_name))
		{
			return $this->modules[$mod_name]->newClass();		
		}
		return false;
	}
	
	public function loadModules(&$sql=null)
	{
	    
		foreach (static::getModules(null,null,$sql) as $mod_desc)
		{
		    
			$mod_desc->loadModule();
			$this->modules[$mod_desc->name] = $mod_desc;
		}
		
	}
	
	public static function getModulesPrefix()
	{
		return $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/modules";
	}

	public static function getModules($type=null, $name=null,$sql=null)
	{
		if (empty($sql)) { $sql = new sql(); }
		$where = null;
		if (isset($type)) { $where .= (isset($where)?" and":"")." `features` like '%".$type."%'"; }
		if (isset($name)) { $where .= (isset($where)?" and":"")." `modName` = '$name'"; }		
		$res = $sql->quickExec("select * from `tblModules` ".(isset($where)?"where ".$where:"")." order by `modPriority` asc");
		if (!$res) { return false;}
		$retVal=[];
		while ($row = mysqli_fetch_assoc($res))
		{
		    $m = new moduleInfo($row);
			$retVal[$m->name] = $m; 
		}
		return (count($retVal) ==0)?false:$retVal;
	}	
	
	public static function getAllModules() {
	    // Load all modules, that exists in /modules directory
	    $mods = scandir($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/modules");
	    $rv=[];
	    foreach ($mods as $key=>$mod) {
	        if (preg_match("/^mod_/", $mod)) {
	            array_push($rv, $mod);
	        }
	    }
	    return $rv;    
        
	}
	
	public static function isModuleInstalled($mod_name)
	{
		if (empty($mod_name)) { return false;}
		$mod = self::getModules(null,$mod_name);
		if ($mod) { return $mod[$mod_name]; } else {return false; }
	}	
	
	public static function loadModule($mod_name)
	{
		$mod = self::isModuleInstalled($mod_name);
		if ($mod)
		{
			if (file_exists($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/modules/mod_".$mod_name."/module.php"))
			{
				try
				{
					require_once($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/modules/mod_".$mod_name."/module.php");
					
					return class_exists("mod_".$mod_name);
				} catch(Exception $err) {
					return false;
				}
			} else { return false; }
			
			print "\n";
		} else {return false;}		
	}
	
	public static function getAuxFiles($module, $type, $filename)
	{
		$file=static::getModulesPrefix()."/mod_".$module."/".$type."/".$filename;
		if (!file_exists($file)) {print "404.21: Not found"; header('HTTP/1.0 404 Not found', true,404); exit; };
		
		$mime_file = mime_content_type($file);
		
		$res = [];
		
		if (preg_match("/.*\.(.*)$/",$file,$res) > 0) {
			if (strtolower($res[1]) == "css") { $mime_file="text/css";}
			elseif(strtolower($res[1]) == "js") { $mime_file="application/javascript";} 
		}
			
	
	  	if (file_exists($file)) {
	   	// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
	   	// если этого не сделать файл будет читаться в память полностью!
	   	if (ob_get_level()) {
	   	  ob_end_clean();
	   	}
	   	// заставляем браузер показать окно сохранения файла
	   	header('Content-Description: File Transfer');	
 	   	header('Content-Type: '. $mime_file);
 	   	header('Content-Disposition: attachment; filename=' . basename($file));
 	   	header('Content-Transfer-Encoding: binary');
    		header('Cache-Control: max-age=14400, must-revalidate');
    		header('Pragma: public');
    		header('Content-Length: ' . filesize($file));
    		// читаем файл и отправляем его пользователю
    		readfile($file);
    		exit;
 	 	} else {}	
	}

	public static function exportSql(?sql &$sql=null) {
	    if (empty($sql)) { $sql = new sql(); }
	    
	    
	}
}

?>