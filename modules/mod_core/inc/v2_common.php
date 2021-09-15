<?php
# !!! FoxAPI V2.0 is deprecated and will be deleted in V4.0
# !!! Please, use FoxAPI V3.0 in new modules

require_once 'coreAPI.php';

use fox\common;
use fox\time as foxtime;
use fox\net as foxnet;
use fox\file as foxfile;


function txt2html($txt) {
    return common::txt2html($txt);
}

// получить значение с get или post
function getVal($name, $regex = '',$skipQuotes=null, $allowEmptyString=true)
{
        return common::getVal($name, $regex, $skipQuotes, $allowEmptyString);
    
}


function dropcslash($val)
{
   return common::dropcslash($val);
}


function mbx_ucfirst($str, $encoding='UTF-8')
{
    return common::mbx_ucfirst($str, $encoding);
}


function getGUIDc()
{
    return common::getGUIDc();
}

function getGUID()
{
  return common::getGUID();
}

function iso_date2datew($isodate)
{
    return foxtime::iso_date2datew($isodate);
}

function iso_date2date($isodate)
{
    return foxtime::iso_date2date($isodate);
}

function iso_date2datetime($isodate)
{
    return foxtime::iso_date2datetime($isodate);
}

function iso_date2datetimew($isodate)
{
    return foxtime::iso_date2datetimew($isodate);
}

function iso_date2datetimesw($isodate)
{
    return foxtime::iso_date2datetimesw($isodate);
}

function iso_date2dates($isodate)
{
    return foxtime::iso_date2dates($isodate);
}

function iso_date2datesz($isodate)
{
    return foxtime::iso_date2datesz($isodate);
}

function stamp2iso_date($stamp=null)
{
    return foxtime::stamp2iso_date($stamp);
}

function iso_date2datesny($isodate)
{
    return foxtime::iso_date2datesny($isodate);
}

function iso_date2stamp($isodate)
{
    return foxtime::iso_date2stamp($isodate);
}

function getCurrStamp()
{
    return foxtime::getCurrStamp();
}


function fullname2qname($first, $mid, $last)
{
    return foxtime::fullname2qname($first, $mid, $last);
}

function text2html($src) {
    return common::text2html($src);
}


function genPasswd($number, $arr=null)
{
    return common::genPasswd($number, $arr);
}

function intToTime($val) {
    return foxtime::intToTime($val);
}

function timeOfDay2sec($timestring) {
    return foxtime::timeOfDay2sec($timestring);
}

function sec2timeOfDay($isec) {
    return foxtime::sec2timeOfDay($isec);
}

function isWorkDay($searchTimestamp,$workDaysOfWeek,$calendarOverride,$ignoreHolidays = false)
{
    return foxtime::isWorkDay($searchTimestamp,$workDaysOfWeek,$calendarOverride,$ignoreHolidays);
}

function getFormatByFilename($filename)
{
    return foxfile::getFormatByFilename($filename);
}


function validateEMail($string)
{
    return common::validateEMail($string);
}

function inet_aton($ip_address)
{
    return foxnet::inet_aton($ip_address);
}

function mac2bin($mac_address)
{
   return foxnet::mac2bin($mac_address);
}

function iso_date2datefmt($date, $fmt=null) {
    return foxtime::iso_date2datefmt($date, $fmt);
}

?>