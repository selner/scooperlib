<?php
/****************************************************************************************************************/
/****                                                                                                        ****/
/****         Helper Functions:  HTTP, HTML, and API Call Utility Functions                                  ****/
/****                                                                                                        ****/
/****************************************************************************************************************/
Namespace Scooper;



////////////////////////////////////////////////////////////
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