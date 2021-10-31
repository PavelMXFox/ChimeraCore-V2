<?php 
global $session, $request, $modules;
print "<pre>";
$mod = $request->parameters[0];
if (!array_key_exists($mod, $modules->modules)) {
    fox\errorPage::show(404);
}
print "Module: ".$mod."\n";
$moddesc=$modules->modules[$mod];
var_dump($moddesc);

$modx = $moddesc->newClass();
print "Title: ".$modx::$title."\n";
print "Desc: ".($modx::$description)."\n";
print "Version: ".($modx::$version)."\n";
print "Main type: ".($modx::$type)."\n";
print "Features: ".json_encode($modx::$features)."\n";

print "ConfigKeys:\n";
var_dump($modx::$configKeys);
print "ACL Rules:\n";
var_dump($modx::$ACLRules);

?>