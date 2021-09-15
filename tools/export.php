<?php 
if (empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) { $_SERVER["CONTEXT_DOCUMENT_ROOT"] = '/var/www/html';}
require_once($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/fox_api.php");
coreModules::getModules();
$sql = new coreSql();

$processed=[];
foreach (coreModules::getModules() as $mod) {
    if (!coreModules::loadModule($mod->loadName)) { continue; }
    if (!in_array($mod->loadName, $processed)) {
        print ";;; Module ".$mod->loadName."\n";
        $processed[count($processed)] = $mod->loadName;
        $module = $mod->newClass();
        $dump=$sql->export($module::$sqlTables);
        if (empty($dump)) { continue;}

        if (!file_exists($module->getPath()."/install")) {
            mkdir($module->getPath()."/install");
        }

        file_put_contents($module->getPath()."/install/install.sql", $dump);
        
        $static_data=[];
        
        foreach ($module::$staticData as $stname=>$stt) {
            foreach ($stt as $stkn=>$stkv) {
            
                $stl="";
                if ($stkv=='all') {
                    $where="";
                } else {
                    foreach ($stkv as $stti) {if (!empty($stl)) { $stl.=",";}; $stl.=$stti;}
                    $where = "where `$stkn` in ($stl)";
                }
                
                $res = $sql->quickExec("select * from `$stname` $where");
                $static_data[$stname]["data"]=[];
                $static_data[$stname]["key"]="$stkn";
                while ($row=mysqli_fetch_assoc($res)) {
                    array_push($static_data[$stname]["data"],$row);
                }
            }
            

            
        }
        
        file_put_contents($module->getPath()."/install/static.json", json_encode($static_data));
    }
}
?>