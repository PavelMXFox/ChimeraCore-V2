<?php

namespace fox;
    

class common {
    
    static function txt2html($txt) {
        
        //We need some HTML entities back!
        $txt = str_replace('&','&amp;',$txt);
        $txt = str_replace('<','&lt;',$txt);
        $txt = str_replace('>','&gt;',$txt);
        $txt = str_replace("\n","<br/>",$txt);
        
        return $txt;
    }
    
    // получить значение с get или post
    static function getVal($name, $regex = '',$skipQuotes=null, $allowEmptyString=true)
    {
        global $_POST, $_GET;
        if ((!isset($_POST[$name]))&&(!isset($_GET[$name])))
        {
            return null;
        }
        if ($regex != "") {
            if (isset($_POST[$name])) { $val = preg_replace('![^'.$regex.']+!', '', $_POST[$name]); } else {$val = ""; }
            if ($val == "") {
                if (isset($_GET[$name])) {$val = preg_replace('![^'.$regex.']+!', '', $_GET[$name]);};
            }
        } else {
            
            if (!isset($skipQuotes)){
                if (isset($_POST[$name])) { $val = preg_replace("![\'\"]+!", '\"', $_POST[$name]);} else {$val="";}
                if ($val == "") {
                    if (isset($_GET[$name])) {$val = preg_replace("![\'\"]+!", '\"', $_GET[$name]);}
                }
            } else {
                if (isset($_POST[$name])) { $val = $_POST[$name];}
                else {
                    $val = $_GET[$name];
                }
                
            }
        }
        if (!$allowEmptyString && $val=="") { $val = null; }
        
        return $val;
        
    }
    
    static function dropcslash($val)
    {
        $val = preg_replace("!\\\([\'\"])+!", "$1", $val);
        return $val;
    }
    
    /**
     * проверяем, что функция mb_ucfirst не объявлена
     * и включено расширение mbstring (Multibyte String Functions)
     */
    
    static function mbx_ucfirst($str, $encoding='UTF-8')
    {
        $str = mb_ereg_replace('^[\ ]+', '', $str);
        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
        mb_substr($str, 1, mb_strlen($str), $encoding);
        return $str;
    }
    
    static function getGUIDc()
    {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
        .substr($charid,16, 4).$hyphen
        .substr($charid,20,12);
        
        return $uuid;
    }
    
    static function getGUID()
    {
        $cUuid = getGUIDc();
        $uuid = chr(123)// "{"
        .$cUuid
        .chr(125);// "}"
        return $uuid;
        
        
    }
      
    static function fullname2qname($first, $mid, $last)
    {
        return $last." ".mb_substr($first,0,1).". ".mb_substr($mid,0,1).".";
    }
    
    static function text2html($src) {
        
        $src=preg_replace("/[\n]/","</br>",$src);
        //	$src=preg_replace("/[\cr\<\>]/"," ",$src);
        return $src;
    }
    
    static function genPasswd($number, $arr=null)
    {
        if (!isset($arr))
        {
            $arr = array('a','b','c','d','e','f',
                'g','h','i','j','k','l',
                'm','n','o','p','r','s',
                't','u','v','x','y','z',
                '1','2','3','4','5','6',
                '7','8','9','0');
        }
        // Генерируем пароль
        $pass = "";
        for($i = 0; $i < $number; $i++)
        {
            // Вычисляем случайный индекс массива
            $index = rand(0, count($arr) - 1);
            $pass .= $arr[$index];
        }
        return $pass;
    }
    
    static function clearInput($val, $regex="") {
        return preg_replace('![^'.$regex.']+!', '', $val);
    }

}
?>