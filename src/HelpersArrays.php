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
    if(!is_array($needle))
    {
        $needle = array($needle);
    }
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
if (!function_exists('array_column')) {

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }

        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }

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

/**
 * Allows multiple expressions to be tested on one string.
 * This will return a boolean, however you may want to alter this.
 *
 * @author William Jaspers, IV <wjaspers4@gmail.com>
 * @created 2009-02-27 17:00:00 +6:00:00 GMT
 * @access public
 *
 * @param array $patterns An array of expressions to be tested.
 * @param String $subject The data to test.
 * @param array $findings Optional argument to store our results.
 * @param mixed $flags Pass-thru argument to allow normal flags to apply to all tested expressions.
 * @param array $errors A storage bin for errors
 *
 * @returns bool Whether or not errors occurred.
 */
function preg_match_multiple(
    array $patterns=array(),
    $subject=null,
    &$findings=array(),
    $flags=false,
    &$errors=array()
) {
    foreach( $patterns as $name => $pattern )
    {
        if( 1 <= preg_match_all( $pattern, $subject, $found, $flags ) )
        {
            $findings[$name] = $found;
        } else
        {
            if( PREG_NO_ERROR !== ( $code = preg_last_error() ))
            {
                $errors[$name] = $code;
            } else $findings[$name] = array();
        }
    }
    return (0===sizeof($errors));
}
