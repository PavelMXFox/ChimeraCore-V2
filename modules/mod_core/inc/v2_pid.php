<?php 

# !!! FoxAPI V2.0 is deprecated and will be deleted in V4.0
# !!! Please, use FoxAPI V3.0 in new modules

function checkPid($pidname)
{
    return fox\pid::checkPid($pidname);
}

function writePid($pidname)
{
    return fox\pid::writePid($pidname);
}

function dropPid($pidname) {
    return fox\pid::dropPid($pidname); 
}

?>