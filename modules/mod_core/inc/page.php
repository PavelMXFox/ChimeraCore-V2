<?php
namespace fox;
require_once 'coreAPI.php';

class page
{
	var $template;
	public $breadcrumbs=null;
	
	public function __construct($template=null)
	{
		if (empty($template) && empty(config::get("defaultTemplate")))
		{
			$template = "core";
			$template = "mod_".$template;		
		}
		
		$this->template = new $template();		
	}	
	
	protected static function writeMenuItem($level, &$mod_desc, &$item, $eAccessKey,$loadName,&$l_ctr,$m_this=false,$pkey=null)
	{
		global $session;
		
		if ($level == 0)
		{

			$title =config::get("menuTitle",$mod_desc->name);
			if (!$title)
			{
				if ($mod_desc->name == $mod_desc->loadName)
				{
					$title = $item["title"];
				} else {
					$title = $item["title"]." (".preg_replace("/^".$mod_desc->loadName."_/","",$mod_desc->name).")";
				}
			}
		} else { $title = $item["title"];}

		if (array_key_exists("access_key",$item))
		{
			$accessKey = $item["access_key"];		
		} elseif (isset($eAccessKey)) { $accessKey = $eAccessKey; 
		} else { $accessKey = $loadName::$globalAccessKey; }
		
		if ($session->checkAccess($accessKey, $mod_desc->name))
		{
		    if (array_key_exists($level, $l_ctr)) { $l_ctr[$level]=2;} else {$l_ctr[$level] = 9; }
		        
	        $menuKey="menu_".$mod_desc->name."_".$pkey;			
			$module = $mod_desc->name.((array_key_exists("function", $item) && !empty($item["function"]))?"/".$item["function"]:"");
			$page_key = $mod_desc->name."_".$item["page_key"];
			

			if (!$m_this) {
			     $m_this=static::searchKey($mod_desc->name, defined("currPage")?currPage:"none", $item, $loadName);
			     
			} else {
			    
			}
			
			print "<p ".(($level==0)?" id='$menuKey"."_".$l_ctr[$level]."'":"")." class='item ".(((defined("currPage") && $page_key == currPage) || ($level==0 && $m_this))?"selected":"").(($level>0)?" ls $menuKey"."_".$l_ctr[$level-1]:"")." l".$level." ".($m_this?"this":"hide")."'>".((array_key_exists("subitems", $item) || array_key_exists("subitems_function", $item))?"<i style='font-size: 10px; vertical-align: middle; margin-bottom: 3px; width: 12px;' class='fas fa-plus'></i><span>$title</span>":"<span style='display: inline-block; width: 7px; margin: 0; padding: 0'></span><a href=".$_SERVER["CONTEXT_PREFIX"]."/".$module.">$title</a></p>");

			if (array_key_exists("subitems", $item))
			{
			    static:: writeMenu($level+1, $item["subitems"], $mod_desc,$accessKey,$loadName,$l_ctr,$m_this,$pkey);
			}
			
			if (array_key_exists("subitems_function", $item)) {
			    $f = ($item["subitems_function"]);
			    static:: writeMenu($level+1, $loadName::$f(), $mod_desc,$accessKey,$loadName,$l_ctr,$m_this,$pkey);
			}
			
			
			
		}
	}
	
	protected static function searchKey($prefix, $key, &$array, $loadname=null) {
	    
	    if ($prefix."_".$array["page_key"] == $key) { return true; }
	    if (array_key_exists("subitems", $array)) {
	        foreach ($array["subitems"] as $i_arr) {
	            if (static::searchKey($prefix, $key, $i_arr)) { return true; }
	        }
	    }
	    
	    if ($loadname && array_key_exists("subitems_function", $array)) {
	        $f = ($array["subitems_function"]);
	        foreach ($loadname::$f() as $i_arr) {
	            if (static::searchKey($prefix, $key, $i_arr)) { return true; }
	        }
	    }
	    return false;
	}
	protected static function writeMenu($level, $menuItem, &$mod_desc,$accessKey=null,$loadName,&$l_ctr=[],$m_this=false,$pkey=null)
	{
		foreach ($menuItem as $key=>$item)
		{
						
			static::writeMenuItem($level, $mod_desc, $item,$accessKey,$loadName,$l_ctr,$m_this,(empty($pkey)?$key:$pkey));
			
		}		

	}
	
	public function loadMainMenu()
	{
		?><p class=title>Основное меню:</p><?php

		foreach (modules::getModules("menu") as $mod_desc){
			if (!$mod_desc->loaded) {continue;}			
			$loadName = "mod_".$mod_desc->loadName;
			static::writeMenu(0,($loadName::$menuItem),$mod_desc,null,$loadName);
		}
		print "<p class='item l0' onClick='doLogoff()' style='cursor: pointer;'>Завершить сеанс</p>";

		return;
	}
	
	public function getTemplate($tag)
	{
		return $this->template->pageTemplate($tag, $this);
	}
	
}
?>