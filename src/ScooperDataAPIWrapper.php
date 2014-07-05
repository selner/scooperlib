<?php
/**
 * Copyright 2014 Bryan Selner
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
Namespace Scooper;

use ErrorException;

const C__STR_USER_AGENT__ = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36";

const C__API_RETURN_TYPE_OBJECT__ = 33;
const C__API_RETURN_TYPE_ARRAY__ = 44;

class ScooperDataAPIWrapper {

    /****************************************************************************************************************/
    /****                                                                                                        ****/
    /****         Helper Functions:  Utility Functions                                                           ****/
    /****                                                                                                        ****/
    /****************************************************************************************************************/

    private function __handleCallback__($callback, &$val, $fReturnType = C__API_RETURN_TYPE_OBJECT__ )
    {

        if($fReturnType == C__API_RETURN_TYPE_ARRAY__)
        {
            $val =  json_decode(json_encode($val, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), true);
        }

        if ($callback && is_callable($callback))
        {
            call_user_func_array($callback, array(&$val));
        }

        if($fReturnType == C__API_RETURN_TYPE_ARRAY__)
        {
            $val = json_decode(json_encode($val, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), false);
        }
    }

    function getObjectsFromAPICall( $baseURL, $objName = "", $fReturnType = C__API_RETURN_TYPE_OBJECT__, $callback = null, $pagenum = 0)
    {
        $retData = null;

        $curl_obj = $this->cURL($baseURL, "", "GET", "application/json", $pagenum);

        $srcdata = json_decode($curl_obj['output']);
        if($srcdata != null)
        {
            if($objName == "")
            {
                if($callback != null)
                {
                    $this->__handleCallback__($callback, $srcdata, $fReturnType);
                }
                $retData = $srcdata;
            }
            else
            {

                foreach($srcdata->$objName as $value)
                {
                    $this->__handleCallback__($callback, $value, $fReturnType);
                    $retData[] = $value;
                }

                //
                // If the data returned has a next_page value, then we have more results available
                // for this query that we need to also go get.  Do that now.
                //
                if($srcdata->next_page)
                {
                    if($GLOBALS['OPTS']['VERBOSE'] == true) { __debug__printLine('Multipage results detected. Getting results for ' . $srcdata->next_page . '...' . PHP_EOL, C__DISPLAY_ITEM_DETAIL__); }

                    // $patternPage = "/.*page=([0-9]{1,})/";
                    $patternPagePrefix = "/.*page=/";
                    // $pattern = "/(\/api\/v2\/).*/";
                    $pagenum = preg_replace($patternPagePrefix, "", $srcdata->next_page);
                    $retSecondary = $this->getObjectsFromAPICall($baseURL, $objName, null, null, $pagenum);

                    //
                    // Merge the primary and secondary result sets into one result
                    // before return.  This allows for multiple page result sets from Zendesk API
                    //

                    foreach($retSecondary as $moreVal)
                    {
                        $this->__handleCallback__($callback, $moreVal, $fReturnType);
                        $retData[] = $moreVal;
                    }
                }
            }
        }


        switch ($fReturnType)
        {
            case  C__API_RETURN_TYPE_ARRAY__:
                $retData = json_decode(json_encode($retData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), true);
                break;


            case  C__API_RETURN_TYPE_OBJECT__:
            default:
                // do nothing;
                break;
        }


        return $retData;
    }



    function cURL($full_url, $json = null, $action = 'GET', $content_type = null, $pagenum = null, $onbehalf = null, $fileUpload = null)
    {


        $curl_object = array('input_url' => '', 'actual_site_url' => '', 'error_number' => 0, 'output' => '', 'output_decoded'=>'');

        if($pagenum > 0)
        {
            $full_url .= "?page=" . $pagenum;
        }
        $header = array();
        if($onbehalf != null) $header[] = ', X-On-Behalf-Of: ' . $onbehalf;
        if($content_type  != null) $header[] = ', X-On-Behalf-Of: ' . $onbehalf;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_USERAGENT, C__STR_USER_AGENT__);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_VERBOSE, $GLOBALS['OPTS']['VERBOSE']);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);

        // curlWrapNew = only?
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);




        switch($action)
        {
            case "POST":

                if($fileUpload != null)
                {
                    $fileh = fopen($fileUpload, 'r');
                    $size = filesize($fileUpload);
                    $fildata = fread($fileh,$size);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fildata);
                    curl_setopt($ch, CURLOPT_INFILE, $fileh);
                    curl_setopt($ch, CURLOPT_INFILESIZE, $size);
                }
                else
                {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                }
                break;
            case "GET":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);


        $output = curl_exec($ch);
        $curl_object['output'] = $output;
        $curl_object['input_url'] = $full_url;
        $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        $curl_object['actual_site_url'] = strtolower($last_url);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch))
        {
            $strErr = 'Error #' . curl_errno($ch) . ': ' . curl_error($ch);
            $curl_object['error_number'] = curl_errno($ch);
            $curl_object['output'] = curl_error($ch);
            curl_close($ch);
            throw new ErrorException($strErr,curl_errno($ch),E_RECOVERABLE_ERROR );
        }     /* If the document has loaded successfully without any redirection or error */
        elseif ($httpCode >= 200 && $httpCode < 300)
        {
            $strErr = "CURL received an HTTP error #". $httpCode;
            $curl_object['http_error_number'] = $httpCode;
            curl_close($ch);
            throw new ErrorException($strErr, E_RECOVERABLE_ERROR );
        }
        else
        {
            curl_close($ch);
        }

        return $curl_object;

    }

}

