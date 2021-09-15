<?php
if (empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) { $_SERVER["CONTEXT_DOCUMENT_ROOT"] = '/var/www/html';}
require_once($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/inc/fox_api.php");
coreModules::getModules();
$sql = new coreSql();

$processed=[];
$modules = coreModules::getModules();
foreach ($modules as $mod) {
    if (!coreModules::loadModule($mod->loadName)) { continue; }
    if (!in_array($mod->loadName, $processed)) {
        $processed[count($processed)] = $mod->loadName;
        $module = $mod->newClass();
        
        $modDesc=$modules[$mod->loadName];
        
        if ($module->check()) {
            print "Module: ".$modDesc->name." OK\n";
        } else {
            
            print "Module: ".$modDesc->name."\n Updating module...\n";
            if ($module->update()) {
                print " Update complete sucessfull!\n";
            } else {
                print " Update failed.\n";
            }
        }
    }
}