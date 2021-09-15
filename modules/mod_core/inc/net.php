<?php

namespace fox;
    

class net {
    static function inet_aton($ip_address)
    {
        $subject = "abcdef";
        $pattern = '/^def/';
        $ip_address = preg_replace('![^'.'0-9.'.']+!', '', $ip_address);
        preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/',$ip_address, $arr);
        if (!(count($arr) ==5 && $arr[1]<256 && $arr[2]<256 && $arr[3]<256 && $arr[4] < 256)) { return false; }
        return $arr[1]*16777216 + $arr[2] * 65536 + $arr[3]*256 + $arr[4];
    }
    
    static function inet_ntoa($num)
    {

        $num = trim($num);
        if ($num == "0") return "0.0.0.0";
        return long2ip(-(4294967295 - ($num - 1)));
        
    }
    
    static function mac2bin($mac_address)
    {
        $mac_address =preg_replace('![^'.'0-9A-Fa-f'.']+!', '', $mac_address);
        if (strlen($mac_address) != 12) { return false; }
        return hex2bin($mac_address);
    }
   
    static function validateEMail($string)
    {
        return preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9.]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $string);
    }
    
    
}
?>