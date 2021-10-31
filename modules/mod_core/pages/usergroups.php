<div class=widget>
<table class="datatable sel"><tr><th>#</th><th>name</th><th>isList</th></tr>
<?php
$agents = fox\userGroup::search("",false,null,999);
$i=0;
foreach ($agents as $u) {
    $i++;
    print "<tr onclick=\"window.location.href=('usergroup/".$u->id."')\">";
    print "<td>".$i."</td>";
    print "<td>".$u->name."</td>";
    print "<td>".($u->isList==1?"L":"A")."</td>";
    
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