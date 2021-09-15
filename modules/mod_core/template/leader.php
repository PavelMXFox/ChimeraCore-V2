<?php

// LEADER
?>
<html <?php print (defined("pgclass")?" class=".pgclass:""); ?>>
<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="<?php print $_SERVER["CONTEXT_PREFIX"]?>/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="<?php print $_SERVER["CONTEXT_PREFIX"]?>/css/core/fontawesome5.css">
	<link rel="stylesheet" href="<?php print $_SERVER["CONTEXT_PREFIX"]?>/css/core/googlefonts.css">
	<link rel="stylesheet" href="<?php print $_SERVER["CONTEXT_PREFIX"]?>/css/core/jquery-ui.css">
	<link rel="stylesheet" href="<?php print $_SERVER["CONTEXT_PREFIX"]?>/css/core/jquery-ui.theme.css">
	<meta name="viewport" content="width=device-width height=device-height user-scalable=yes"/>
	<link href="<?php print $_SERVER["CONTEXT_PREFIX"]?>/css/core/jquery.datetimepicker.min.css" type="text/css" rel="stylesheet">
	<link href="<?php print $_SERVER["CONTEXT_PREFIX"]?>/css/core/main.css" type="text/css" rel="stylesheet">
	<title><?php print coreConfig::get('title'); ?></title>
	<script src="<?php print $_SERVER["CONTEXT_PREFIX"]?>/js/core/jquery.min.js"></script>
	<script src="<?php print $_SERVER["CONTEXT_PREFIX"]?>/js/core/jquery-ui.min.js"></script>
	<script src="<?php print $_SERVER["CONTEXT_PREFIX"]?>/js/core/jquery.datetimepicker.full.min.js"></script>
	<script src="<?php print $_SERVER["CONTEXT_PREFIX"]?>/js/core/main.js"></script>
	
	<?php
		// Loading theme
	if (isset($session) && $session->auth->user->uiTheme) {
		  $theme=$session->auth->user->uiTheme;
	    } else if (coreConfig::get("defaultTheme")) {
	        $theme=coreConfig::get("defaultTheme");
	    } else {
	        $theme = "core/chimera";
	    }
		
		
		$theme = explode("/", $theme, 2);
		
		if (count($theme)==2) {
		  $th_module = $theme[0];
		  $th_theme = $theme[1];
		} else {
		    $th_module="none";
		    $th_theme="none";
		}
	
	
		$th_url=$_SERVER["CONTEXT_PREFIX"]."/css/".$th_module."/theme_".$th_theme.".css";
	
		if (!file_exists($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/modules/mod_".$th_module."/css/theme_".$th_theme.".css"))
		{
			$th_url=$_SERVER["CONTEXT_PREFIX"]."/css/core/theme_chimera.css";
		}
	
			
		
		 print"<link rel=\"stylesheet\" href=\"".$th_url."\">";
		 
	?>
</head>

<script type="text/javascript" >

var sitePrefix="<?php print $_SERVER["CONTEXT_PREFIX"]; ?>";
var modInstance="<?php print (isset($request)?$request->module:"") ?>";
var modFunction="<?php print (isset($request)?$request->function:"") ?>";

var xSession="<?php print str_pad(strtoupper(dechex(crc32(empty($session)?"empty":$session->sid))),8,"0",STR_PAD_LEFT); ?>"; 
var xInstance="<?php print str_pad(strtoupper(dechex(crc32($_SERVER["HTTP_HOST"]))),8,"0",STR_PAD_LEFT); ?>";

function doLogoff()
{
	 jsonExec('<?php print $_SERVER["CONTEXT_PREFIX"]?>/ajax/auth/logoff', null , function (data) {
	 	document.cookie= data.cookie_name+'=""; path=/; max-age=-1';
	 	window.location.reload(false); 
	 });
}
</script>

<body <?php print defined("pgclass")?" class=".pgclass:""; ?>>
<div class="blanker bggray"></div>
<div class="blanker bgmain">
<div class="bl_infobox">
<img src="<?php print $_SERVER["CONTEXT_PREFIX"]?>/img/core/ajax.gif"/>
<h1 id="blanker_text">Ваш запрос обрабатывается</h1></div>
</div>
