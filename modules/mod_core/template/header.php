<?php 
// HEADER
global $session;
?>
<script language=javascript>
$(document).ready(function(){
	$("p.item.l0").click(menuitem_Click);
	menuUpdateMarkers();
	//$("p.ls.hide").hide();
})

function menuUpdateMarkers() {
	$("p.l0").addClass("hide");
	$("p.this").removeClass("hide");
	//$("p.curr").addClass("this");
	$("p.l0.hide i").removeClass("fa-minus");
	$("p.l0.hide i").addClass("fa-plus");

	$("p.l0.this i").removeClass("fa-plus");
	$("p.l0.this i").addClass("fa-minus");
	$("p.this").removeClass("this");
}

function menuitem_Click(me) {
	$("p.this").removeClass("this");
	$("p.ls").addClass("hide");
	//$("p.ls.curr").removeClass("hide");

	if($(this).hasClass("hide")) {
		$(this).addClass("this");
		$("p.ls."+this.id).removeClass("hide");
	} else {
		$(this).addClass("hide");
		$("p.ls."+this.id).addClass("hide");
	}
	menuUpdateMarkers();
	

	

}
</script>
<div class="hidden_menu">
<?php
	$page->loadMainMenu();
	
?>
</div>

<div class="t_global">
	<div class="header">
		<div class="logo_menu"><i class="fas fa-bars"></i></div>
		<div class="header_logo"><?php print coreConfig::get("title"); ?></div>
	</div>

	<div class="t_navy_main clicktohide"><?php $page->loadMainMenu(); ?></div>
	<div class="t_main clicktohide">
		<div class="breadcrumbs">
			<div style="display: inline-block; float: right; padding-right: 10px;"><a href="<?php print $_SERVER["CONTEXT_PREFIX"];?>/core/myprofile" ><?php print $session->auth->user->fullName;?><i class="far fa-user" style="margin-left: 10px; font-size: 15px;"></i></a></div>
			<div style="display: inline-block; float: right; padding-right: 10px;"><i class="fas fa-bell alert" ></i></div>
			<div style="display: inline-block; float: left;  position: absolute; max-height: 18px; overflow: hidden; "><i class="far fa-lightbulb" style="margin-right: 10px; font-size: 15px;"></i><span id="breadcrumbs_label"><?php print (defined("breadcrumbs")?breadcrumbs:"Chimera FOX"); ?></span></div>
		</div>