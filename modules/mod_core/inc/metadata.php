<?php
namespace fox;


class metadata
{
    
    static function get($key, $module)
    {
        $res = sql::sqlQuickExec1Line("select `value` from `tblMetadata` where `module` = '$module' and `key` = '$key'");
        if ($res) { return $res["value"];};
        return null;
    }
    
    static function getAll($module)
    {
        $res = sql::sqlQuickExec("select `key`,`value` from `tblMetadata` where `module` = '$module'");
        $config = [];
        while ($row = mysqli_fetch_assoc($res))
        {
            $config[$row["key"]] = $row["value"];
        }
        return $config;
    }
    
    static function set($key, $value, $module)
    {
        global $coreAPI;
        $sql = $coreAPI->SQL;
        if (self::get($key,$module) !== null)
        {
            $sql->prepareUpdate("tblMetadata");
            $sql->paramAddUpdate("value",$value);
            $sql->paramClose(" `module` = '".$module."' and `key` = '".$key."'");
            $sql->execute();
        } else {
            $sql->prepareInsert("tblMetadata");
            $sql->paramAddInsert("module",$module);
            $sql->paramAddInsert("key",$key);
            $sql->paramAddInsert("value",$value);
            $sql->paramClose();
            $sql->execute();
        }
    }
    
    static function del($key, $module)
    {
        sql::sqlQuickExec("delete from `tblMetadata` where `module` = '$module' and `key`='$key'");
    }
    
    static function delAll($module)
    {
        sql::sqlQuickExec("delete from `tblMetadata` where `module` = '$module'");
    }
    
}
