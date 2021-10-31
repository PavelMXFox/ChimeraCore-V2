<?php
spl_autoload_register(function ($name) {
    $r=[];
    $rg = "/("."fox"."\\\)([^\\\]*)/";
    if (preg_match($rg, $name, $r)) {
        include_once($r[2].".php");
    }
});
    
    ?>