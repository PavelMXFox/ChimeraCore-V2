<div class=widget>
<table class="datatable sel"><tr><th>#</th><th>address</th><th>module</th><th>D</th></tr>
<?php
$agents = fox\mailAccount::search("",null,999);
$i=0;
foreach ($agents as $u) {
    $i++;
    print "<tr onclick=\"window.location.href=('mailaccount/".$u->id."')\">";
    print "<td>".$i."</td>";
    print "<td><pre>".$u->address."</pre></td>";
    print "<td>".$u->module."</td>";
    print "<td>".($u->default==1?"D":"")."</td>";
    
    /*
     * 
    print "<td>".(strlen($mod->desc)==0?$mod->name:$mod->desc)."</td>";
    print "<td>".$mod->name."</td>";
    print "<td>".$mod->loadName."</td>";
    print "<td class='code'>".$mod->version."</td>";
    print "<td>".json_encode($mod->type)."</td>";
    *
    */
    
}
?>
</table>
</div>