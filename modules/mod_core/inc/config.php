<?php
namespace fox;
use Exception;
require_once("coreAPI.php");
	
class config
{

	static function get($key, $module="core")
	{
	global $foxConfig;
		if (empty($foxConfig))
		{
		    include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/settings.php");
		}
		
		if (empty($foxConfig)) { throw new Exception("Error: Config file not found"); }
		if (!array_key_exists("sqlServer",$foxConfig) || empty($foxConfig["sqlServer"])) { throw new Exception("Error: SQL config not found"); }
		if ($module == "core" && array_key_exists($key, $foxConfig))
		{
			return $foxConfig[$key];		
		}
		else {
			$res = sql::sqlQuickExec1Line("select `value` from `tblSettings` where `module` = '$module' and `key` = '$key'");
			if ($res) { return $res["value"];};
			return null;
		}
	}
	
	static function getAll($module="core")
	{
		#if (empty($foxConfig))
		#{
		#    include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/settings.php");
		#}
		$res = sql::sqlQuickExec("select `key`,`value` from `tblSettings` where `module` = '$module'");
		$config = [];
		while ($row = mysqli_fetch_assoc($res))
		{
			$config[$row["key"]] = $row["value"];
		}
		return $config;
	}
	
	static function set($key, $value, $module)
	{
	    global $coreAPI;
	    $sql = $coreAPI->SQL;
	    if (metadata::get($key,$module) !== null)
	    {
	        $sql->prepareUpdate("tblSettings");
	        $sql->paramAddUpdate("value",$value);
	        $sql->paramClose(" `module` = '".$module."' and `key` = '".$key."'");
	        $sql->execute();
	    } else {
	        $sql->prepareInsert("tblSettings");
	        $sql->paramAddInsert("module",$module);
	        $sql->paramAddInsert("key",$key);
	        $sql->paramAddInsert("value",$value);
	        $sql->paramClose();
	        $sql->execute();
	    }
	}
	
	static function del($key, $module)
	{
	    sql::sqlQuickExec("delete from `tblSettings` where `module` = '$module' and `key`='$key'");
	}
	
	static function delAll($module)
	{
	    sql::sqlQuickExec("delete from `tblSettings` where `module` = '$module'");
	}

}
?>