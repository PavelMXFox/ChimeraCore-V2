<?php
define("pgclass","login");
include 'leader.php';

global $request;

?>

<script type="text/javascript" src="<?php print $_SERVER["CONTEXT_PREFIX"]?>/js/core/login.js"></script>

<script type="text/javascript" >
pg_module='<?php print $request->module;?>';
pg_function='<?php print $request->function;?>';

$(document).on('keypress',function(e) {
    if(e.which == 13) {
       doLogin();
    }   
});

$(document).ready(function() {
   if (pg_module=='login' && pg_function=="") {
 		window.location.href=sitePrefix+"/";
	} else if (pg_function == 'recover') {
		recoverComplete('<?php print getVal('code');?>', {login: '<?php print getVal('login'); ?>'});
	} else if (pg_function == 'regconfirm') {
		userRegConfirm('<?php print getVal('code');?>', {login: '<?php print getVal('login'); ?>'});
	} else if (pg_function == 'register') {
		userRegister('<?php print getVal('code');?>');
	}
});



</script>
</head>

<div style=' text-align: center; width: 100%; heigth: 100%;'>
<!-- <img src='<?php print $_SERVER["CONTEXT_PREFIX"]?>/img/core/chimera_logo.svg' style="height: 95%; width: 95%; margin-left: -5%; opacity: 100%;"/>
-->
</div>

<form method=post id=f_login>
<input type=hidden name=a value="login"/>
<div class="login" style="padding: 0px;">
<h2 class="login first" style="display: inline-block; float: left">Авторизация</h2>
<div class="button short super green" style="float: right; margin-right: 0; background-color: rgba(var(--mxs-bg-rgba-prefix),1);" onclick="doLogin()" Title="Войти"><i class="fas fa-sign-in-alt"></i></div>
<div class="widget">
<?php
	if (isset($authstatus))
	{
		print "<div style='color: red; font-size: 20px;'>$authstatus</div>";	
	}
?>
<div class="crm_entity_block_group">
<div class="crm_entity_field_block">
<div class="crm_entity_field_title"><span>Логин</span></div>
<div class="crm_entity_field_value"><input id=username name=login></input>
</div>					
</div>

<div class="crm_entity_field_block">
<div class="crm_entity_field_title"><span>Пароль</span></div>
<div class="crm_entity_field_value"><input type=password id=password name=password></input>
</div>					
</div>
</div>

</div>

<div class=widget>
<span class='linkButton' onClick="restorePasswd()">Восстановить</span> <?php if (fox\config::get("allowRegister")===true && null !== fox\config::get("registerModule")) {?> или <span class="linkButton" onClick="userRegister()">Зарегистрироваться</span><?php } ?>
</div>

</div>
</form>  
  </body>

<div style=" position: absolute; display: block; bottom: 32; text-align: center; width: 100%; font-family: 'Jura', sans-serif; font-size: 16px; color: #024c68;">Powered by Chimera FOX</div>

<?php
include "footer.php";
?>