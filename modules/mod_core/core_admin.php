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
			    
						
		}
		
		if (!defined("currPage")) { define ("currPage",$this->instance.(empty($request->function)?"_main":"_".$request->function));}
		if (!defined("breadcrumbs")) { define ("breadcrumbs",$this->instance.(empty($request->function)?"":" / ".$request->function));}

	
		$page = new corePage();		

		$page->getTemplate("leader");

		switch ($request->function) {
		    case "myprofile":
		        ?>
<script src="<?php print $_SERVER["CONTEXT_PREFIX"]."/js/core/myprofile.js"?>"></script>

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
		        
		        
		}
		
		// include($this->modPath."/inc/core_test_body.php");
		$page->getTemplate("footer");
?>