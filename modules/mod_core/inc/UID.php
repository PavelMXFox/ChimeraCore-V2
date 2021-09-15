<?php
namespace fox;
use Exception;
require_once("coreAPI.php");

class UID {
    public static function issue($module, $class, $ref_id=null,?sql &$sql=null) {
        self::checkSql($sql);
        if (!modules::isModuleInstalled($module)) { throw new Exception("Module $module is not installed!"); }

        $id=null;
        if (isset($ref_id)) {
            $id = $sql->quickExec1Line("select `uid` from `tblUidRegistry` where `module`= '$module' and `class` = '$class' and `ref_id` = '$ref_id'");
            if ($id) {
                $id = $id['uid'];
            }
        }
     
        if (empty($id)) {
            $sql->prepareInsert("tblUidRegistry");
            $sql->paramAdd("module", $module);
            $sql->paramAdd("class", $class);
            if (isset($ref_id)) { $sql->paramAdd("ref_id", $ref_id);}
            $sql->execute();
            $id = $sql->getInsertId();
        }
        
        $id .= self::checksum($id);
        return $id;
    }
    
    public static function check($code) {
        $code = static::clear($code);
        if (strlen($code) != 10) {return false;}
        return (substr($code, 9, 1)==self::checksum($code));		
    }
    
    public static function print($code) {
        return (substr($code, 0, 4)."-".substr($code, 4, 4)."-".substr($code, 8, 2));

    }

    public static function link($code, $id, ?sql &$sql=null) {
        self::checkSql($sql);
        $code = self::clear($code);
        if (!self::check($code)) { return -1;}
        $s_code = substr($code,0,9);
        return $sql->quickExec("UPDATE `tblUidRegistry` SET `ref_id` = '$id' where `uid` = '$s_code'");
    }
    
    public static function lost($code, ?sql &$sql=null) {
        self::checkSql($sql);
        $code = self::clear($code);
        if (!self::check($code)) { return -1;}
        $s_code = substr($code,0,9);
        return $sql->quickExec("DELETE FROM  `tblUidRegistry` where `uid` = '$s_code'");
    }
   
    public static function clear($code) {
        return preg_replace('![^0-9]+!', '', $code);
    }
    
    protected function checkSql(?sql &$sql) {
        if (!((gettype($sql) == 'object') && (get_class($sql) == 'coreSql'))) {
            $sql = new sql();
        }
    }

    protected static function checksum($code) {
        $sum1 = substr($code, 1, 1)+substr($code, 3, 1)+substr($code, 5, 1)+substr($code, 7, 1);
        $sum1 = $sum1*3;
        $sum2 = substr($code, 0, 1)+substr($code,2, 1)+substr($code, 4, 1)+substr($code, 6, 1)+substr($code, 8, 1);
        $sum = $sum1+$sum2;
        $ceil = ceil(($sum/10))*10;
        $delta = $ceil - $sum;
        return $delta;	
    }
    
}
?>