<?php
/****************************************************************************************************************/
/****                                                                                                        ****/
/****         Helper Functions:  HTTP, HTML, and API Call Utility Functions                                  ****/
/****                                                                                                        ****/
/****************************************************************************************************************/
Namespace Scooper;

use Exception;


function getDefaultFileName($strFilePrefix, $strBase, $strExt)
{
    $strApp = "";
    if(C__APPNAME__ != "" && C__APPNAME__ != null && strcasecmp(C__APPNAME__, "C__APPNAME__") != 0) { $strApp = C__APPNAME__ . "_"; } else { $strApp = "ScooperLib_"; }
    return sprintf($strApp . date("Ymd-Hms")."%s_%s.%s", ($strFilePrefix != null ? "_".$strFilePrefix : ""), ($strBase != null  ? "_".$strBase : ""), $strExt);
}


function getFullPathFromFileDetails($arrFileDetails, $strPrependToFileBase = "", $strAppendToFileBase = "")
{
    return $arrFileDetails['directory'] . getFileNameFromFileDetails($arrFileDetails, $strPrependToFileBase, $strAppendToFileBase);

}

function getFileNameFromFileDetails($arrFileDetails, $strPrependToFileBase = "", $strAppendToFileBase = "")
{
    return $strPrependToFileBase . $arrFileDetails['file_name_base'] . $strAppendToFileBase . "." . $arrFileDetails['file_extension'];
}

function __construct()
{
}

function parseFilePath($strFilePath, $fFileMustExist = false)
{
    $fileDetails = array ('full_file_path' => '', 'directory' => '', 'file_name' => '', 'file_name_base' => '', 'file_extension' => '');

    if(strlen($strFilePath) > 0)
    {
        if(is_dir($strFilePath))
        {
            $fileDetails['directory'] = $strFilePath;
        }
        else
        {

            // separate into elements by '/'
            $arrFilePathParts = explode("/", $strFilePath);

            if(count($arrFilePathParts) <= 1)
            {
                $fileDetails['directory'] = ".";
                $fileDetails['file_name'] = $arrFilePathParts[0];
            }
            else
            {
                // pop the last element (the file name + extension) into a string
                $fileDetails['file_name'] = array_pop($arrFilePathParts);

                // put the rest of the path parts back together into a path string
                $fileDetails['directory']= implode("/", $arrFilePathParts);
            }

            if(strlen($fileDetails['directory']) == 0 && strlen($fileDetails['file_name']) > 0 && file_exists($fileDetails['file_name']))
            {
                $fileDetails['directory'] = dirname($fileDetails['file_name']);

            }

            if(!is_dir($fileDetails['directory']))
            {
                print('Specfied path '.$strFilePath.' does not exist.'.PHP_EOL);
            }
            else
            {
                // since we have a directory and a file name, combine them into the full file path
                $fileDetails['full_file_path'] = $fileDetails['directory'] . "/" . $fileDetails['file_name'];

                // separate the file name by '.' to break the extension out
                $arrFileNameParts = explode(".", $fileDetails['file_name']);

                // pop off the extension
                $fileDetails['file_extension'] = array_pop($arrFileNameParts );

                // put the rest of the filename back together into a string.
                $fileDetails['file_name_base'] = implode(".", $arrFileNameParts );


                if($fFileMustExist == true && !is_file($fileDetails['full_file_path']))
                {
                    print('Required file '.$fileDetails['full_file_path'].' does not exist.'.PHP_EOL);
                }
            }
        }
    }

    // Make sure the directory part ends with a slash always
    $strDir = $fileDetails['directory'];

    if((strlen($strDir) >= 1) && $strDir[strlen($strDir)-1] != "/")
    {
        $fileDetails['directory'] = $fileDetails['directory'] . "/";
    }

    return $fileDetails;

}



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
     $ret = null;
    $ret = \Scooper\parseFilePath($fullpath, $fRequireFile);

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
    $valOpt = get_PharseOptionValue($optUserKeyName);
    if($valOpt) $ret = \Scooper\parseFilePath($valOpt, $fFileRequired);

    return $ret;

}
