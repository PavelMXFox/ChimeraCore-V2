<?php

namespace fox;


class pid {
    public static function checkPid($pidname)
    {
        return file_exists("/var/run/".$pidname.".pid");
    }

    public static function writePid($pidname)
    {
        system("echo ".getmypid()." > /var/run/".$pidname.".pid");
    }

    public static function dropPid($pidname)
    {
        if (checkPid($pidname))
        {
        system("rm /var/run/".$pidname.".pid");
        }
    }
}
?>