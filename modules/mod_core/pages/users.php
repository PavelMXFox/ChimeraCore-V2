<div class=widget>
<table class="datatable sel"><tr><th>#</th><th>UID</th><th>login</th><th>eMail</th><th>fullName</th><th>A</th><th>D</th><th>R</th></tr>
<?php
$agents = fox\user::search("",null,null,999);
$i=0;
foreach ($agents as $u) {
    $i++;
    print "<tr onclick=\"window.location.href=('user/".$u->id."')\">";
    print "<td>".$i."</td>";
    print "<td>".$u->invCode."</td>";
    print "<td>".$u->login."</td>";
    print "<td>".$u->eMail."</td>";
    
    print "<td>".$u->fullName."</td>";
    print "<td>".($u->isActive==1?"A":"")."</td>";
    print "<td>".($u->isDeleted==1?"D":"")."</td>";
    print "<td>".($u->isRegistered==1?"R":"")."</td>";
    
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