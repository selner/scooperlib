<?php
/****************************************************************************************************************/
/****                                                                                                        ****/
/****         Helper Functions:  HTTP, HTML, and API Call Utility Functions                                  ****/
/****                                                                                                        ****/
/****************************************************************************************************************/
Namespace Scooper;

use Exception;

function substr_count_array( $haystack, $needle ) {
    $count = 0;
    foreach ($needle as $substring) {
        $count += substr_count( $haystack, $substring);
    }
    return $count;
}

function is_array_multidimensional($a)
{
    if(!is_array($a)) return false;
    foreach($a as $v) if(is_array($v)) return TRUE;
    return FALSE;
}

const C_ARRFLAT_SUBITEM_NONE__ = 0;
const C_ARRFLAT_SUBITEM_SEPARATOR__ = 1;
const C_ARRFLAT_SUBITEM_LINEBREAK__ = 2;


function array_flatten($arr, $strDelim = '|', $flagsSubItems=C_ARRFLAT_SUBITEM_NONE__)
{
    $keys = array_keys($arr);
    $values= array_values($arr);
    $output = array();
    foreach ($keys as $key => $item)
    {
        $newVal = $values[$key];
        if(is_array($newVal))
        {
            if(is_array_multidimensional($newVal))
            {
                $outputVal = array_flatten($newVal, $strDelim, $flagsSubItems );
            }
            else
            {
                $outputVal = implode($strDelim, $newVal);
            }
        }
        else
        {
            $outputVal = $newVal;
        }
        $fIncludeLineBreaks = (substr_count($outputVal, "|") > 1 && ($flagsSubItems & C_ARRFLAT_SUBITEM_LINEBREAK__));
        $fIncludeSeparators = (substr_count($outputVal, "|") > 1 && ($flagsSubItems & C_ARRFLAT_SUBITEM_SEPARATOR__));
        $output[$key] = ($fIncludeLineBreaks ? "\n" : "") . ($fIncludeSeparators ? "(" : "") . $outputVal . ($fIncludeSeparators ? ")" : "");
    }
    $ret = implode($strDelim, $output);

    return $ret;
}



function array_to_object($d) {
    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return (object) array_map(__FUNCTION__, $d);
    }
    else {
        // Return object
        return $d;
    }
}
function object_to_array($d) {
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    }
    else {
        // Return array
        return $d;
    }
}


if (!function_exists('array_copy'))
{
// Source: http://www.php.net/manual/en/ref.array.php#81081

    /**
     * make a recursive copy of an array
     *
     * @param array $aSource
     * @return array    copy of source array
     * @throws Exception if array is not valid
     */
    function array_copy ($aSource) {
        // check if input is really an array
        if (!is_array($aSource)) {
            throw new Exception("Input is not an Array");
        }

        // initialize return array
        $aRetAr = array();

        // get array keys
        $aKeys = array_keys($aSource);
        // get array values
        $aVals = array_values($aSource);

        // loop through array and assign keys+values to new return array
        for ($x=0;$x<count($aKeys);$x++) {
            // clone if object
            if (is_object($aVals[$x])) {
                $aRetAr[$aKeys[$x]]=clone $aVals[$x];
                // recursively add array
            } elseif (is_array($aVals[$x])) {
                $aRetAr[$aKeys[$x]]=array_copy ($aVals[$x]);
                // assign just a plain scalar value
            } else {
                $aRetAr[$aKeys[$x]]=$aVals[$x];
            }
        }

        return $aRetAr;
    }
}


function my_merge_add_new_keys( $arr1, $arr2 )
{
     // check if inputs are really arrays
    if (!is_array($arr1) || !is_array($arr2)) {
    }
    $strFunc = "my_merge_add_new_keys(arr1(size=".count($arr1)."),arr2(size=".count($arr2)."))";
    if(isset($GLOBALS['logger'])) $GLOBALS['logger']->logLine($strFunc, C__DISPLAY_FUNCTION__, true);
    $arr1Keys = array_keys($arr1);
    $arr2Keys = array_keys($arr2);
    $arrCombinedKeys = array_merge_recursive($arr1Keys, $arr2Keys);

    $arrNewBlankCombinedRecord = array_fill_keys($arrCombinedKeys, 'unknown');

    $arrMerged =  array_replace( $arrNewBlankCombinedRecord, $arr1 );
    $arrMerged =  array_replace( $arrMerged, $arr2 );

    if(isset($GLOBALS['logger'])) $GLOBALS['logger']->logLine('returning from ' . $strFunc, C__DISPLAY_FUNCTION__, true);
    return $arrMerged;
}

/*
 * Flattening a multi-dimensional array into a
 * single-dimensional one. The resulting keys are a
 * string-separated list of the original keys:
 *
 * a[x][y][z] becomes a[implode(sep, array(x,y,z))]
 */

function array_flatten_sep($sep, $array) {
    $result = array();
    $stack = array();
    array_push($stack, array("", $array));

    while (count($stack) > 0)
    {
        list($prefix, $array) = array_pop($stack);

        foreach ($array as $key => $value)
        {
            $new_key = $prefix . strval($key);

            if (is_array($value))
                array_push($stack, array($new_key . $sep, $value));
            else
                $result[$new_key] = $value;
        }
    }

    return $result;
}
