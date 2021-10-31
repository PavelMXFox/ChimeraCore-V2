<?php

		switch ($request->function)
		{				
			case "myprofile":
				if (!defined("currPage")) { define ("currPage",$this->instance.(empty($request->function)?"_main":"_".$request->function));}
				if (!defined("breadcrumbs")) { define ("breadcrumbs","Мой профиль");}
				break;
				
			case "modules":
			    if (!defined("currPage")) { define ("currPage",$this->instance.(empty($request->function)?"_main":"_".$request->function));}
			    if (!defined("breadcrumbs")) { define ("breadcrumbs","Модули");}
			    break;

			case "users":
			    if (!defined("currPage")) { define ("currPage",$this->instance.(empty($request->function)?"_main":"_".$request->function));}
			    if (!defined("breadcrumbs")) { define ("breadcrumbs","Пользователи");}
			    break;
			    
			case "groups":
			    if (!defined("currPage")) { define ("currPage",$this->instance.(empty($request->function)?"_main":"_".$request->function));}
			    if (!defined("breadcrumbs")) { define ("breadcrumbs","Группы");}
			    break;
			    
			case "mail":
			    if (!defined("currPage")) { define ("currPage",$this->instance.(empty($request->function)?"_main":"_".$request->function));}
			    if (!defined("breadcrumbs")) { define ("breadcrumbs","Почтовые настройки");}
			    break;

			case "module":
			    if (!defined("currPage")) { define ("currPage",$this->instance."_modules");}
			    if (!defined("breadcrumbs")) { define ("breadcrumbs","Модуль / ");}
			    break;

			case "user":
			    if (!defined("currPage")) { define ("currPage",$this->instance."_users");}
			    if (!defined("breadcrumbs")) { define ("breadcrumbs","Пользователь / ");}
			    break;
			    
			case "usergroup":
			    if (!defined("currPage")) { define ("currPage",$this->instance."_groups");}
			    if (!defined("breadcrumbs")) { define ("breadcrumbs","Группа /  ");}
			    break;
			    
		}
		
		if (!defined("currPage")) { define ("currPage",$this->instance.(empty($request->function)?"_main":"_".$request->function));}
		if (!defined("breadcrumbs")) { define ("breadcrumbs",$this->instance.(empty($request->function)?"":" / ".$request->function));}

		
		trigger_error(currPage);
	
		$page = new corePage();		

		$page->getTemplate("leader");

		switch ($request->function) {
		    case "myprofile":
		        ?>
<script src="<?php print $_SERVER["CONTEXT_PREFIX"]."/js/core/myprofile.js"?>"></script>

<?php 
        break;
        
        case "user":
    ?>
<script src="<?php print $_SERVER["CONTEXT_PREFIX"]."/js/core/pguser.js"?>"></script>

<?php 
        break;

        case "usergroup":
    ?>
<script src="<?php print $_SERVER["CONTEXT_PREFIX"]."/js/core/pggroup.js"?>"></script>

<?php 
        break;
        
		}
		
		$page->getTemplate("header");
		
		switch ($request->function)
		{
		    case "myprofile":
		        include $this->modPath."/pages/myprofile.php";
		        break;
		        
		    case "modules":
		        include $this->modPath."/pages/modules.php";
		        break;

		    case "users":
		        include $this->modPath."/pages/users.php";
		        break;
		        
		    case "groups":
		        include $this->modPath."/pages/usergroups.php";
		        break;

		    case "mail":
		        include $this->modPath."/pages/mailaccounts.php";
		        break;
		        
		    case "module":
		        include $this->modPath."/pages/module.php";
		        break;
		        
		    case "user":
		        include $this->modPath."/pages/user.php";
		        break;
		        
		    case "usergroup":
		        include $this->modPath."/pages/usergroup.php";
		        break;
		        
		}
		
		// include($this->modPath."/inc/core_test_body.php");
		$page->getTemplate("footer");
?>