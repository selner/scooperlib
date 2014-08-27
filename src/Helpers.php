<?php
/****************************************************************************************************************/
/****                                                                                                        ****/
/****         Helper Functions:  HTTP, HTML, and API Call Utility Functions                                  ****/
/****                                                                                                        ****/
/****************************************************************************************************************/
Namespace Scooper;

use Exception;

function isBitFlagSet($flagSettings, $flagToCheck)
{
    $ret = ($flagSettings & $flagToCheck);
    if($ret == $flagToCheck) { return true; }
    return false;
}


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

CONST C__FILEPATH_NO_FLAGS = 0x0;
CONST C__FILEPATH_FILE_MUST_EXIST = 0x1;
CONST C__FILEPATH_DIRECTORY_MUST_EXIST = 0x2;
CONST C__FILEPATH_CREATE_DIRECTORY_PATH_IF_NEEDED= 0x4;

function parseFilePath($strFilePath, $fFileMustExist = false)
{
    return getFilePathDetailsFromString($strFilePath, ($fFileMustExist ? C__FILEPATH_FILE_MUST_EXIST : C__FILEPATH_NO_FLAGS));
}

function getFilePathDetailsFromString($strFilePath, $flags = C__FILEPATH_NO_FLAGS)
{
    $fileDetailsReturn = array ('directory' => '', 'has_directory' => false, 'file_name' => '', 'has_file' => false, 'file_name_base' => '', 'file_extension' => '', 'full_file_path' => '' );


    if($strFilePath == null || strlen($strFilePath) <= 0)
    {
        return $fileDetailsReturn;
    }

    // if the path doesn't start with a '/', it's a relative path
    //
    $fPathIsRelative = !(substr($strFilePath, 0, 1) == '/');

    //************************************************************************
    //
    // First, pull the path string apart into it's component directories and possible filename
    // by separating the path elements by '/'
    $arrInputPathAllParts = explode("/", $strFilePath);

    // Setup a string value for the last element (usually a filename, but could be directory)
    //
    $finalPathPart_String = $arrInputPathAllParts[count($arrInputPathAllParts)-1];

    // Setup array value for the last element separated by '.'.  We'll assume that if there
    // was a '.' then the last element was a filename, not a directory (and vice versa.)
    //
    $finalPathPart_DotArray = $arrLastTermParts = explode(".", $finalPathPart_String);

    // Lastly, set an array value for all the directory parts minus the last one
    //
    $arrPathParts_AllButFinal = $arrInputPathAllParts;  // copy the full list of parts and then...
    unset($arrPathParts_AllButFinal[count($arrPathParts_AllButFinal)-1]); // ... remove the last part


    //************************************************************************
    //
    // Now let's figure out what each part really maps to and setup the array with names for returning
    // to the caller.
    //
    // If AllParts only has one item, then there were no "/" characters in the path string.
    // So assume the path was either a filename only OR a relative directory path with no trailing '/'
    //
    if(substr($strFilePath, (strlen($strFilePath) - 1), 1) == '/' || // if the path ended with a / or...
        count($finalPathPart_DotArray) == 1) // ... only the last part had no '.' so isn't a filename
    {
        //
        // There was no filename on the input path
        //
        $fileDetailsReturn['has_file'] = false;

        // add any beginning path parts to the directory path...
        if(count($arrPathParts_AllButFinal) > 0)
        {
            $strDirectory = join("/", $arrPathParts_AllButFinal);
            // and add the final part to the end
            $strDirectory .= "/" . $finalPathPart_String;
        }
        else // otherwise, the directory is just the final part
        {
            $strDirectory = $finalPathPart_String;
        }
        $fileDetailsReturn['has_directory'] = true;
        $fileDetailsReturn['directory'] = $strDirectory;
    }
    else // we have a filename at least
    {
        assert(count($finalPathPart_DotArray) > 1);

        // we did have a '.' so let's assume this term is a filename
        $fileDetailsReturn['file_name'] = $finalPathPart_String;

        // the last portion of the split filename is the extension
        $fileDetailsReturn['file_extension'] = $finalPathPart_DotArray[count($finalPathPart_DotArray)-1];

        // everything else is the base name for the file
        $fileDetailsReturn['file_name_base'] = join(".", array_splice($finalPathPart_DotArray,0,count($finalPathPart_DotArray)-1));
        $fileDetailsReturn['has_file'] = true;


        // Set the directory part to everything before the last part
        if(count($arrPathParts_AllButFinal) > 0)
        {
            // if the first part is "" then the path part
            // was actually "/<something>" so put the / back
            if(count($arrPathParts_AllButFinal) == 1 && strlen($arrPathParts_AllButFinal[0]) == 0)
            {
                $fileDetailsReturn['directory'] = "/";
            }
            $fileDetailsReturn['directory'] .= join("/", $arrPathParts_AllButFinal);
            $fileDetailsReturn['has_directory'] = true;
        }

        // if there were no other parts, so set the directory to be relative to the file
        if($fileDetailsReturn['has_directory'] == false)
        {
            $fileDetailsReturn['directory'] = "./";
            $fileDetailsReturn['has_directory'] = true;
        }
    }

    assert($fileDetailsReturn['has_directory'] == true);

    // Make sure the directory value always ends with a slash
    // (makes it easier for callers to depend on it)
    //
    if((strlen($fileDetailsReturn['directory']) >= 1) &&
        $fileDetailsReturn['directory'][strlen($fileDetailsReturn['directory'])-1] != "/")
    {
        $fileDetailsReturn['directory'] = $fileDetailsReturn['directory'] . "/";
    }

    if($fileDetailsReturn['has_file'])
    {
        $fileDetailsReturn['full_file_path'] = $fileDetailsReturn['directory'] . $fileDetailsReturn['file_name'];


        assert($fileDetailsReturn['file_name'] == $fileDetailsReturn['file_name_base'] . "." . $fileDetailsReturn['file_extension']);
        assert($fileDetailsReturn['full_file_path'] == $fileDetailsReturn['directory'] . $fileDetailsReturn['file_name_base'] . "." . $fileDetailsReturn['file_extension']);

    }
    else
    {
        $fileDetailsReturn['full_file_path'] = '';
    }


    //
    // At this point, we've set the values for the return array completely
    //


    if(isBitFlagSet($flags, C__FILEPATH_DIRECTORY_MUST_EXIST) && !is_dir($fileDetailsReturn['directory']))
    {
        throw new \ErrorException("Directory '" . $fileDetailsReturn['directory'] . "' does not exist.");
    }

    if(isBitFlagSet($flags, C__FILEPATH_FILE_MUST_EXIST) && !is_file($fileDetailsReturn['full_file_path']))
    {
        throw new \ErrorException("File '" . $fileDetailsReturn['full_file_path'] . "' does not exist.");
    }

    if(isBitFlagSet($flags, C__FILEPATH_CREATE_DIRECTORY_PATH_IF_NEEDED) && !is_dir($strFilePath))
    {
        mkdir($fileDetailsReturn['directory'], 0777, true);
    }




    return $fileDetailsReturn;

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
define('ADVANCED_TEXT_CLEANUP', HTML_DECODE | REMOVE_EXTRA_WHITESPACE | REMOVE_PUNCT );
define('FOR_LOOKUP_VALUE_MATCHING', REMOVE_PUNCT | LOWERCASE | HTML_DECODE | REMOVE_EXTRA_WHITESPACE | REMOVE_ALL_SPACES );
define('DEFAULT_SCRUB', REMOVE_PUNCT | HTML_DECODE | LOWERCASE | REMOVE_EXTRA_WHITESPACE );

//And so on, 0x8, 0x10, 0x20, 0x40, 0x80, 0x100, 0x200, 0x400, 0x800 etc..


function strScrub($str, $flags = null)
{
    if($flags == null)  $flags = REMOVE_EXTRA_WHITESPACE;

    if(strlen($str) == 0) return $str;
    
    // If this isn't a valid string we can process,
    // log a warning and return the value back to the caller untouched.
    //
    if($str == null || !isset($str) || !is_string($str))
    {
        if(isset($GLOBALS['logger'])) $GLOBALS['logger']->logLine("strScrub was called with an invalid value to scrub (not a string, null, or similar.  Cannot scrub the passed value: " . var_export($str, true), C__DISPLAY_WARNING__);
        return $str;
    }

    $ret = $str;


    if ($flags & HTML_DECODE)
    {
        $ret = html_entity_decode($ret);
    }

    if ($flags & REMOVE_PUNCT)  // has to come after HTML_DECODE
    {
        $ret = \Scooper\strip_punctuation($ret);
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
    if(isset($GLOBALS['OPTS']) && isset($GLOBALS['OPTS'][$strOptGiven]) && $GLOBALS['OPTS'][$strOptGiven] == true)
    {
        if(isset($GLOBALS['logger']) && isset($GLOBALS['VERBOSE'])) $GLOBALS['logger']->logLine("'".$strOptName ."'"."=[".$GLOBALS['OPTS'][$strOptName] ."]", C__DISPLAY_ITEM_DETAIL__);
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

    if(isset($GLOBALS['logger'])) $GLOBALS['logger']->logLine("". $key ." set to [" . var_export($ret, true) . "]", C__DISPLAY_ITEM_DETAIL__);

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

//    $fMatched = preg_match("/^['|\"]([^'\"]{1,})['|\"]$/", $valOpt, $arrMatches);
//    if($fMatched) $valOpt = $arrMatches[1];

    if($valOpt) $ret = \Scooper\parseFilePath($valOpt, $fFileRequired);

    return $ret;

}

/**
 * Strip punctuation from text.
 * http://nadeausoftware.com/articles/2007/9/php_tip_how_strip_punctuation_characters_web_page
 */
function strip_punctuation( $text )
{
    $urlbrackets    = '\[\]\(\)';
    $urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
    $urlspaceafter  = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
    $urlall         = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;

    $specialquotes  = '\'"\*<>';

    $fullstop       = '\x{002E}\x{FE52}\x{FF0E}';
    $comma          = '\x{002C}\x{FE50}\x{FF0C}';
    $arabsep        = '\x{066B}\x{066C}';
    $numseparators  = $fullstop . $comma . $arabsep;

    $numbersign     = '\x{0023}\x{FE5F}\x{FF03}';
    $percent        = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
    $prime          = '\x{2032}\x{2033}\x{2034}\x{2057}';
    $nummodifiers   = $numbersign . $percent . $prime;

    return preg_replace(
        array(
            // Remove separator, control, formatting, surrogate,
            // open/close quotes.
            '/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
            // Remove other punctuation except special cases
            '/\p{Po}(?<![' . $specialquotes .
            $numseparators . $urlall . $nummodifiers . '])/u',
            // Remove non-URL open/close brackets, except URL brackets.
            '/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
            // Remove special quotes, dashes, connectors, number
            // separators, and URL characters followed by a space
            '/[' . $specialquotes . $numseparators . $urlspaceafter .
            '\p{Pd}\p{Pc}]+((?= )|$)/u',
            // Remove special quotes, connectors, and URL characters
            // preceded by a space
            '/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
            // Remove dashes preceded by a space, but not followed by a number
            '/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
            // Remove consecutive spaces
            '/ +/',
        ),
        ' ',
        $text );
}

/**
 * copied from <a href="http://php.net/manual/en/function.system.php">http://php.net/manual/en/function.system.php</a>
 * returns an array of stdout, stderr, and return value from the systemcall
 */
function my_exec($cmd, $input='') {
    $proc=proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
    fwrite($pipes[0], $input);fclose($pipes[0]);
    $stdout=stream_get_contents($pipes[1]);fclose($pipes[1]);
    $stderr=stream_get_contents($pipes[2]);fclose($pipes[2]);
    $rtn=proc_close($proc);
    return array(
        'stdout'=>$stdout,
        'stderr'=>$stderr,
        'return'=>$rtn
    );
}


function getTodayAsString()
{
    return date("Y-m-d");
}


function intceil($number)
{
    if(is_string($number)) $number = floatval($number);

    $ret = ( is_numeric($number) ) ? ceil($number) : false;
    if ($ret != false) $ret = intval($ret);

    return $ret;
}
