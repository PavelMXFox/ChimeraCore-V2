<?php 
namespace fox;

interface APITokenIface {
    public function getUUID();
    public function sign($subject);
    public function validate($subject, $sign);
}

?>