<div class=widget>
<table class="datatable sel"><tr><th>#</th><th>Prio</th><th>Desc</th><th>Instance</th><th>Module</th><th>Version</th><th>Functions</th></tr>
<?php
$agents = fox\modules::getModules();
$i=0;
foreach ($agents as $mod) {
    $i++;
    print "<tr onclick=\"window.location.href=('module/".$mod->name."')\">";
    print "<td>".$i."</td>";
    print "<td>".$mod->priority."</td>";
    print "<td>".(strlen($mod->desc)==0?$mod->name:$mod->desc)."</td>";
    print "<td>".$mod->name."</td>";
    print "<td>".$mod->loadName."</td>";
    print "<td class='code'>".$mod->version."</td>";
    print "<td>".json_encode($mod->type)."</td>";
}
?>
</table>
</div>