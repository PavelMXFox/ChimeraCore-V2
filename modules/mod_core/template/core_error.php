<?php
define("pgclass","login");
include 'leader.php';
?>

<div style=' text-align: center; width: 100%; heigth: 100%;'>

</div>
<!--<div style=" position: absolute; display: block; top: 0; text-align: center; width: 100%; font-family: 'Teko', sans-serif; font-size: 32pt; color: #024c68; padding: 20 0 0 0; margin: -1%;">MX FOX V2.0<div style="width: 150px; display: inline-block;"></div> CHIMERA</div>
-->
<div style=" 
	border: 2px solid red;
   background-color: rgba(0, 0, 0, 0.85);
	
	margin: 0; 
	padding: 70 0 0 0; 
	position: absolute;
	left: calc(50% - 250px);
	display: block; 
	top: calc(50% - 75px); 
	text-align: center; 
	width: 500px;
	height: 150px;
	vertical-align: center; 
	font-family: 'Fira Mono', monospace; 
	font-weight: 200; 
	font-size: 32px; color: #FF3500; margin: 1%">
ERROR: <?php print $error_code; ?><br/>
<?php print $error_desc; ?></div>

<?php
header('HTTP/1.0 '.$error_code.' '.$error_desc, true,$error_code);
exit;