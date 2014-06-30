<?php
/****************************************************************************************************************/
/****                                                                                                        ****/
/****         Helper Functions:  HTTP, HTML, and API Call Utility Functions                                  ****/
/****                                                                                                        ****/
/****************************************************************************************************************/
Namespace Scooper;

use Exception;


////////////////////////////////////////
//
// Modified
// Original Source for getPrimaryDomainFromUrl:  http://php.net/parse_url
// Author: webmaster at bigbirdmedia dot com
// Modified to support returning the domain name minus the top level domain
////////////////////////////////////////////////////////////
if (!function_exists('getPrimaryDomainFromUrl'))
{

    function getPrimaryDomainFromUrl($url, $fIncludeTLD = true)
    {
        if(strlen($url) <= 5) return null;
        if(substr_count_array($url, array("http://", "https://")) < 1) { $url = "http://".$url;}
        $tld = parse_url($url, PHP_URL_HOST);
        $tldArray = explode(".",$tld);

        // COUNTS THE POSITION IN THE ARRAY TO IDENTIFY THE TOP LEVEL DOMAIN (TLD)
        $l1 = '0';
        $l2 = null;

        foreach($tldArray as $s)
        {
            $s = str_replace("/", "", $s);
            // CHECKS THE POSITION IN THE ARRAY TO SEE IF IT MATCHES ANY OF THE KNOWN TOP LEVEL DOMAINS (YOU CAN ADD TO THIS LIST)
            if($s == 'com' || $s == 'net' || $s == 'info' || $s == 'biz' || $s == 'us' || $s == 'co' || $s == 'org' || $s == 'me')
            {

                // CALCULATES THE SECOND LEVEL DOMAIN POSITION IN THE ARRAY ONCE THE POSITION OF THE TOP LEVEL DOMAIN IS IDENTIFIED
                $l2 = $l1 - 1;
            }
            else {
                // INCREMENTS THE COUNTER FOR THE TOP LEVEL DOMAIN POSITION IF NO MATCH IS FOUND
                $l1++;
            }
        }

        // RETURN THE SECOND LEVEL DOMAIN AND THE TOP LEVEL DOMAIN IN THE FORMAT LIKE "SOMEDOMAIN.COM"
        $strReturnDomain = $tldArray[$l2];
        if($fIncludeTLD == true) { $strReturnDomain = $strReturnDomain . '.' . $tldArray[$l1]; }
        return $strReturnDomain;

    }
}



/*
0x20 : 00100000
0x10 : 00010000
0x08 : 00001000
0x04 : 00000100
0x02 : 00000010
0x01 : 00000001
*/

define('REMOVE_PUNCT', 0x001);
define('LOWERCASE', 0x002);
define('HTML_DECODE', 0x004);
define('URL_ENCODE', 0x008);
define('REPLACE_SPACES_WITH_HYPHENS', 0x010);
define('REMOVE_EXTRA_WHITESPACE', 0x020);
define('REMOVE_ALL_SPACES', 0x040);
define('SIMPLE_TEXT_CLEANUP', HTML_DECODE | REMOVE_EXTRA_WHITESPACE );
define('ADVANCED_TEXT_CLEANUP', HTML_DECODE | REMOVE_EXTRA_WHITESPACE | REMOVE_PUNCT | REMOVE_EXTRA_WHITESPACE | HTML_DECODE );
define('FOR_LOOKUP_VALUE_MATCHING', REMOVE_PUNCT | LOWERCASE | HTML_DECODE | LOWERCASE | REMOVE_EXTRA_WHITESPACE | REMOVE_ALL_SPACES );
define('DEFAULT_SCRUB', REMOVE_PUNCT | HTML_DECODE | LOWERCASE | REMOVE_EXTRA_WHITESPACE );

//And so on, 0x8, 0x10, 0x20, 0x40, 0x80, 0x100, 0x200, 0x400, 0x800 etc..


function strScrub($str, $flags = null)
{
    if($flags == null)  $flags = REMOVE_EXTRA_WHITESPACE;
    $ret = $str;


    if ($flags & HTML_DECODE)
    {
        $ret = html_entity_decode($ret);
    }

    if ($flags & REMOVE_PUNCT)  // has to come after HTML_DECODE
    {
        $ret = strip_punctuation($ret);
    }

    if ($flags & REMOVE_ALL_SPACES)
    {
        $ret = trim($ret);
        if($ret != null)
        {
            $ret  = str_replace(" ", "", $ret);
        }
    }

    if ($flags & REMOVE_EXTRA_WHITESPACE)
    {
        $ret = trim($ret);
        if($ret != null)
        {
            $ret  = str_replace(array("   ", "  ", "    "), " ", $ret);
            $ret  = str_replace(array("   ", "  ", "    "), " ", $ret);
        }
        $ret = trim($ret);
    }


    if ($flags & REPLACE_SPACES_WITH_HYPHENS) // has to come after REMOVE_EXTRA_WHITESPACE
    {
        $ret  = str_replace(" ", "-", $ret); // do it twice to catch the multiples
    }


    if ($flags & LOWERCASE)
    {
        $ret = strtolower($ret);
    }

    if ($flags & URL_ENCODE)
    {
        $ret  = urlencode($ret);
    }

    return $ret;
}




function get_PharseOptionValue($strOptName)
{
    $retvalue = null;
    $strOptGiven = $strOptName."_given";
    if($GLOBALS['OPTS'][$strOptGiven] == true)
    {
        $GLOBALS['logger']->logLine("'".$strOptName ."'"."=[".$GLOBALS['OPTS'][$strOptName] ."]", C__DISPLAY_ITEM_DETAIL__);
        $retvalue = $GLOBALS['OPTS'][$strOptName];
    }
    else
    {
        $retvalue = null;
    }

    return $retvalue;
}

function setGlobalFileDetails($key, $fRequireFile = false, $fullpath = null)
{
    $classFileInfo = new ScooperFileInfo();    $ret = null;
    $ret = $classFileInfo->parseFilePath($fullpath, $fRequireFile);

    $GLOBALS['logger']->logLine("". $key ." set to [" . var_export($ret, true) . "]", C__DISPLAY_ITEM_DETAIL__);

    $GLOBALS['OPTS'][$key] = $ret;

    return $ret;
}

function set_FileDetails_fromPharseSetting($optUserKeyName, $optDetailsKeyName, $fFileRequired)
{
    $valOpt = get_PharseOptionValue($optUserKeyName);
    return setGlobalFileDetails($optDetailsKeyName, $fFileRequired, $valOpt);
}


function get_FileDetails_fromPharseOption($optUserKeyName, $fFileRequired)
{
    $ret = null;
    $classFileInfo = new ScooperFileInfo();
    $valOpt = get_PharseOptionValue($optUserKeyName);
    if($valOpt) $ret = $classFileInfo->parseFilePath($valOpt, $fFileRequired);

    return $ret;

}
