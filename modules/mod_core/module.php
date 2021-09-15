<?php
require_once($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/fox_api.php");

class mod_core extends baseModule
{
	public static $title="Core Description meta-package";
	public static $description="Пакет описания базовой системы";
	public static $version="3.0.4";
	public static $type="core";
	public static $globalAccessKey=null;

	public static $themes=[
		"chimera" => [
			"css"=>"theme-chimera.css",
		],
		"fennec" => [
			"css"=>"theme-fennec.css",	
		]	
	];
	
	public static $menuItem=[
		"core_settings"=> [
			"title"=>"Настройки системы",
			"function" => "",
			"access_key" => "core_admin",
			"page_key"=>"main",
		   # "subitems_function"=>"getModulesMenu",
            "subitems" => [
                [
                    "title"=>"Модули",
                    "function"=>"modules",
                    "page_key"=>"modules",
                ],
            ]
		    
		],
	];
	
	public static $sqlTables=[
	    "tblAccessRights",
	    "tblCompany",
	    "tblConfigRefTypes",
	    "tblUidRegistry",
	    "tblGroupRightsLink",
	    "tblModules",
	    "tblSessions",
	    "tblSettings",
	    "tblUserGroupLink",
	    "tblUserGroups",
	    "tblUsers",
	    "tblLogs",
	    "tblMetadata",
	    "tblFiles",
	    "tblMailAccounts",
	    "tblMailMessages",
	    "tblAuthTickets",
	    "tblAPITokens",
	    "tblDocuments",
	    "tblDocumentsAck"

	];

	public static $features=["theme","menu","page","core","login"];

	public static $staticData=[
	    "tblCompany"=>["id"=>[1]],
	    "tblModules"=>["id"=>[1,2,3]],
	    "tblUidRegistry"=>["uid"=>[100000000]],
	    "tblUserGroups"=>["id"=>[1]],
	    "tblGroupRightsLink"=>["id"=>[1,2]],
	];
	
	public function loginPage()
	{
		include($this->modPath."/template/core_login.php");
	}

	public function errorPage($error_code=null, $error_desc=null,$error_code_clean=null)
	{
	    if (empty($error_code_clean)) { $error_code_clean=$error_code; };
	    include($this->modPath."/template/core_error.php");
	}
	
	public function pageTemplate($tag, &$page)
	/*
		tag: [ leader, header,footer ]
	*/
	{
		global $request, $session;

		switch ($tag)
		{
			case "leader":
				include($this->modPath."/template/leader.php");
				return true;
				break;
			case "header":
				include($this->modPath."/template/header.php");
				return true;
				break;
			case "footer":
				include($this->modPath."/template/footer.php");
				return true;
				break;			
		}	
	}
	
	public function mainPage()
	{
		global $request;
		include("core_admin.php");
	}

	public function ajaxPage() {
	    global $request;
	    include "ajax/ajax.php";
	}
	
	public function authAjaxPage() {
	    include "ajax/auth.php";
	}

	public static function getModulesMenu() {
	    $rv=[];
	    print "<pre>";
	    foreach(fox\modules::getModules() as $mod_name=>$mod) {
	        array_push($rv,[
	            "title"=>$mod->name,
	            "function"=>"module_".$mod->id,
	            "page_key"=>"module_".$mod->id,
	        ]);
	    }
	    return $rv;
    }
}	
?>