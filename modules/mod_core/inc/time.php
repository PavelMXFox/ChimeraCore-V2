<?php

namespace fox;
    

class time {
    
    static function iso_date2datew($isodate)
    {
        if ($isodate === null || $isodate == '')
        {
            return "Never";
        }
        $t = strtotime($isodate);
        $marr = ['мартобря','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
        $dow=['ХЗ','Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
        
        $isodate = preg_replace('![^0-9]+!', '', $isodate);
        
        $date = $dow[date("N",$t)]." ". (substr($isodate,6,2)+0)." "
            .$marr[(substr($isodate,4,2)+0)]." "
                .substr($isodate,0,4)." г.";
                
                return $date;
    }
    
    static function iso_date2date($isodate)
    {
        if ($isodate === null || $isodate == '')
        {
            return "Never";
        }
        $marr = ['мартобря','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
        
        $isodate = preg_replace('![^0-9]+!', '', $isodate);
        
        $date = (substr($isodate,6,2)+0)." "
            .$marr[(substr($isodate,4,2)+0)]." "
                .substr($isodate,0,4)." г.";
                
                return $date;
    }
    
    static function iso_date2datetime($isodate)
    {
        $marr = ['мартобря','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
        
        $isodate = preg_replace('![^0-9]+!', '', $isodate);
        
        $date = (substr($isodate,6,2)+0)." "
            .$marr[(substr($isodate,4,2)+0)]." "
                .substr($isodate,0,4)." г. "
                    .substr($isodate,8,2).":"
                        .substr($isodate,10,2).":"
                            .substr($isodate,12,2);
                            
                            return $date;
    }
    
    static function iso_date2datetimew($isodate)
    {
        $t = strtotime($isodate);
        $marr = ['мартобря','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
        $dow=['ХЗ','Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
        
        $isodate = preg_replace('![^0-9]+!', '', $isodate);
        
        $date = $dow[date("N",$t)]." ".(substr($isodate,6,2)+0)." "
            .$marr[(substr($isodate,4,2)+0)]." "
                .substr($isodate,0,4)." г. "
                    .substr($isodate,8,2).":"
                        .substr($isodate,10,2).":"
                            .substr($isodate,12,2);
                            
                            
                            return $date;
    }
    
    static function iso_date2datetimesw($isodate)
    {
        $t = strtotime($isodate);
        $marr = ['мтб','янв','фев','мар','апр','мая','июн','июл','авг','сен','окт','ноя','дек'];
        $dow=['ХЗ','Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
        
        $isodate = preg_replace('![^0-9]+!', '', $isodate);
        
        $date = $dow[date("N",$t)]." ".(substr($isodate,6,2)+0)." "
            .$marr[(substr($isodate,4,2)+0)]." "
                .substr($isodate,0,4)." г. "
                    .substr($isodate,8,2).":"
                        .substr($isodate,10,2).":"
                            .substr($isodate,12,2);
                            
                            
                            return $date;
    }
    
    static function iso_date2dates($isodate)
    {
        if ($isodate === null || $isodate == '')
        {
            return "Never";
        }
        $marr = ['мтб','янв','фев','мар','апр','мая','июн','июл','авг','сен','окт','ноя','дек'];
        
        $isodate = preg_replace('![^0-9]+!', '', $isodate);
        
        $date = (substr($isodate,6,2)+0)." "
            .$marr[(substr($isodate,4,2)+0)]." "
                .substr($isodate,0,4)." г.";
                
                return $date;
    }
    
    static function iso_date2datesz($isodate)
    {
        $marr = ['мтб','янв','фев','мар','апр','мая','июн','июл','авг','сен','окт','ноя','дек'];
        
        $isodate = preg_replace('![^0-9]+!', '', $isodate);
        if ((substr($isodate,6,2)+0) < 10) {$add = "0";} else {$add = "";};
        $date = $add.(substr($isodate,6,2)+0)." "
            .$marr[(substr($isodate,4,2)+0)]." "
                .substr($isodate,0,4)." г.";
                
                return $date;
    }
    
    static function stamp2iso_date($stamp=null)
    {
        if (empty($stamp)) { $stamp = time(); }
        return date("Y-m-d H:i:s",$stamp);
    }
    
    static function iso_date2datesny($isodate)
    {
        $marr = ['мтб','янв','фев','мар','апр','мая','июн','июл','авг','сен','окт','ноя','дек'];
        
        $isodate = preg_replace('![^0-9]+!', '', $isodate);
        
        $date = (substr($isodate,6,2)+0)." "
            .$marr[(substr($isodate,4,2)+0)];
            
            return $date;
    }
    
    static function iso_date2stamp($isodate)
    {
        // 01234567890123
        // 20180901235959
        $isodate = preg_replace('![^0-9]+!', '', $isodate);
        
        //$date = (substr($isodate,6,2)+0)." "
        //	    .$marr[(substr($isodate,4,2)+0)];
        $date = mktime(intval((substr($isodate,8,2))), intval(substr($isodate,10,2)),intval(substr($isodate,12,2)), intval((substr($isodate,4,2))), intval((substr($isodate,6,2))), intval(substr($isodate,0,4)));
        
        return $date;
    }
    
    static function getCurrStamp()
    {
        return time();
    }
   
    static function intToTime($val) {
        $hrs = intdiv ($val , 60 );
        $mins = $val-($hrs*60);
        
        $retval="$hrs:".sprintf("%'.02d",$mins);
        return $retval;
    }
    
    static function timeOfDay2sec($timestring) {
        $hrs = +(substr($timestring, 0, 2));
        $min = +(substr($timestring, 3, 2));
        $sec = +(substr($timestring, 6, 2));
        $stampsec = ($hrs*3600)+($min*60)+$sec;
        
        return $stampsec;
    }
    
    static function sec2timeOfDay($isec) {
        $hrs = floor($isec/3600);
        $min = floor(($isec - ($hrs*3600))/60);
        $sec = $isec - ($hrs*3600) - ($min*60);
        
        return "$hrs:$min:$sec";
    }
    
    static function isWorkDay($searchTimestamp,$workDaysOfWeek,$calendarOverride,$ignoreHolidays = false)
    {
        //$searchTimestamp = strtotime($startISODATETIME);
        
        if ($ignoreHolidays)
        {
            return true;
        }
        // 1. Определяем день недели
        $dayOfWeek = date("N",$searchTimestamp);
        
        // 2. проверяем вхождение для в список рабочих дней недели или исключений
        $searchDate = date("Y-m-d",$searchTimestamp);
        
        if (array_key_exists($searchDate,$calendarOverride))
        {
            $isWorkDay = $calendarOverride[$searchDate];
        } else {
            $isWorkDay = preg_match ( "/".$dayOfWeek."/" , $workDaysOfWeek);
        }
        return $isWorkDay;
    }
    
    static function iso_date2datefmt($date, $fmt=null) {
        // return printable representaion of $this->updated
        if ($date === null || $date == '')
        {
            return "Never";
        }
        
        switch ($fmt) {
            case "s":
                return self::iso_date2dates($date);
                break;
            case "sny":
                return self::iso_date2datesny($date);
                break;
            case "sz":
                return self::iso_date2datesz($date);
                break;
            case "w":
                return self::iso_date2datew($date);
                break;
            case "t":
                return self::iso_date2datetime($date);
                break;
            case "tw":
                return self::iso_date2datetimew($date);
                break;
            case "tsw":
                return self::iso_date2datetimesw($date);
                break;
            default:
                return self::iso_date2date($date);
                break;
        }
    }
    
    
}

?>