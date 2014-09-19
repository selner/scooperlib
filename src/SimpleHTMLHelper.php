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

namespace Scooper;
use SimpleHtmlDom;
use ErrorException;

const C__SIMPLEHTML_THROWEXCEPTION = 0x1;
const C__SIMPLEHTML_NOTFOUND_RETURN_EMPTYSTR = 0x2;
const C__SIMPLEHTML_NOTFOUND_RETURN_NULL = 0x4;
const C__SIMPLEHTML_FOUND_RETURN_PLAINTEXT = 0x8;
const C__SIMPLEHTML_FOUND_RETURN_PROPERTY = 0x10;
const C__SIMPLEHTML_FOUND_RETURN_ATTRIB = 0x20;
const C__SIMPLEHTML_FOUND_RETURN_NODE = 0x40;
const C__SIMPLEHTML_FOUND_RETURN_ALLCHILDREN = 0x80;

class SimpleHTMLHelper
{
    private $nodeObj = null;
    private $url = null;
    private $html = null;
    private $timeDownload = null;

    function getHTML() { return $this->html; }
    function getDownloadTime() { return $this->timeDownload; }
    function getSimpleHtmlDOMNodeObject() { return $this->nodeObj; }

    function __construct($objParam)
    {
        if(isset($objParam))
        {
            if(is_string($objParam))
            {
                $this->url = $objParam;
                $class = new \Scooper\ScooperDataAPIWrapper();
                $timeDownloadStart= time();
                $retOutput = $class->cURL($this->url,'' , 'GET', 'application/html');
                if(isset($retOutput) && $retOutput['error_number'] == 0)
                {
                    $timeDownloadEnd = time();
                    $this->timeDownload = $timeDownloadEnd - $timeDownloadStart;
                    $this->html = $retOutput['output'];
                    $this->nodeObj = \SimpleHtmlDom\str_get_html($this->html, false, true, DEFAULT_TARGET_CHARSET, false);
               }
                else
                {
                    var_dump($retOutput);
                    exit("Error downloading URL" . $this->url);
                }
            }
            elseif(is_object($objParam) && strcasecmp(get_class($objParam), "SimpleHtmlDom\\simple_html_dom") == 0)
            {
                $this->nodeObj = $objParam;

            }
        }


        if(!isset($objParam) || $objParam == false)
        {
            throw new ErrorException("Required simple_html_dom_node or simple_html_dom object was not set.");
        }

    }

    function __destruct()
    {
        if(isset($this->nodeObj) && is_object($this->nodeObj) && strcasecmp(get_class($this->nodeObj), "SimpleHtmlDom") == 0)
            $this->nodeObj->clear();
    }

    function get($strNodePath, $retIndex = null, $fRequired = true)
    {
        $flags = C__SIMPLEHTML_FOUND_RETURN_NODE;
        if($fRequired == true)  $flags = $flags | C__SIMPLEHTML_THROWEXCEPTION ;
        return $this->getNodeValue($strNodePath, $retIndex, $flags);
    }

    function getSimpleNode($strNodePath, $retIndex = null, $fRequired = true)
    {
        $node = $this->get($strNodePath, $retIndex, $fRequired);
        if(isset($node))
        {
            if(is_array($node) && count($node) >= 1 && strcasecmp(get_class($node[0]),'SimpleHtmlDom\simple_html_dom_node') == 0)
            {
                $retArr = array();
                foreach($node as $nd)
                {
                    $retArr[] = new SimpleHTMLHelper($nd);
                }
                return $retArr;
            }
            else
            {
                return new SimpleHTMLHelper($node);
            }
        }
        else
        {
            throw new ErrorException("Could not create new SimpleHTMLHelper for ". $strNodePath);
        }
        return null;
    }

    function getText($strNodePath, $retIndex, $fRequired = false, $optPropOrAttrName = null)
    {
        $flags = C__SIMPLEHTML_NOTFOUND_RETURN_EMPTYSTR | C__SIMPLEHTML_FOUND_RETURN_PLAINTEXT;
        if($fRequired == true)  $flags = $flags | C__SIMPLEHTML_THROWEXCEPTION ;

        return $this->getNodeValue($strNodePath, $retIndex, $flags, "plaintext", $optPropOrAttrName);
    }

    function getAllChildrenText($strNodePath, $retIndex, $fRequired = false)
    {
        $flags = C__SIMPLEHTML_NOTFOUND_RETURN_EMPTYSTR | C__SIMPLEHTML_FOUND_RETURN_ALLCHILDREN;
        if($fRequired == true)  $flags = $flags | C__SIMPLEHTML_THROWEXCEPTION ;

        return $this->getNodeValue($strNodePath, $retIndex, $flags);
    }

    function getProperty($strNodePath, $retIndex, $optPropOrAttrName, $fRequired = false)
    {
        $flags = C__SIMPLEHTML_NOTFOUND_RETURN_EMPTYSTR | C__SIMPLEHTML_FOUND_RETURN_PROPERTY;
        if($fRequired == true)  $flags = $flags | C__SIMPLEHTML_THROWEXCEPTION ;

        return $this->getNodeValue($strNodePath, $retIndex, $flags, $optPropOrAttrName);
    }

    function getAttribute($strNodePath, $retIndex, $optPropOrAttrName, $fRequired = false)
    {
        $flags = C__SIMPLEHTML_NOTFOUND_RETURN_EMPTYSTR | C__SIMPLEHTML_FOUND_RETURN_ATTRIB;
        if($fRequired == true)  $flags = $flags | C__SIMPLEHTML_THROWEXCEPTION ;

        return $this->getNodeValue($strNodePath, $retIndex, $flags, $optPropOrAttrName);
    }

    function getNodeValue($strNodePath, $retIndex, $flags, $optPropOrAttrName = null)
    {
        $ret = null;
        if(\Scooper\isBitFlagSet($flags,C__SIMPLEHTML_NOTFOUND_RETURN_EMPTYSTR ))
        {
            $ret = "";
        }

        try
        {
            if(isset($strNodePath) && strlen($strNodePath) > 0)
            {
                $subNode = $this->nodeObj->find($strNodePath);
            }
            else
            {
                $subNode = $this->nodeObj;
            }

            if(!isset($subNode))
            {
                throw new ErrorException("Failed to find expected node path: " & $strNodePath);
            }



            if(\Scooper\isBitFlagSet($flags, C__SIMPLEHTML_FOUND_RETURN_NODE ))
            {
                $ret = $subNode;
                if(isset($retIndex) && isset($subNode[$retIndex]))
                {
                    $ret = $subNode[$retIndex];
                }
            }
            else
            {
                $subNodeElement = null;

                if(isset($retIndex) && isset($subNode[$retIndex]))
                {
                    $subNodeElement = $subNode[$retIndex];
                }
                elseif(isset($retIndex) && !isset($subNode[$retIndex]))
                {
                    $this->throwExceptIfRequired("Node element (" . $strNodePath .")[" . $retIndex . "] was not found.", $flags);
                }
                else
                {
                    $subNodeElement = $subNode;
                }
                if(isVerbose())
                {
                    print ("Node path(" . (isset($strNodePath)?$strNodePath :"null").")[index=".(isset($retIndex)?$retIndex:"null")."] => " .PHP_EOL);
                    \SimpleHtmlDom\dump_html_tree($subNodeElement);
                    print ("<= end " .PHP_EOL);
                }

                if(\Scooper\isBitFlagSet($flags, C__SIMPLEHTML_FOUND_RETURN_PROPERTY ))
                {
                    if(!isset($optPropOrAttrName) || !isset($subNodeElement->$optPropOrAttrName))
                    {
                        $this->throwExceptIfRequired("Property '" . $optPropOrAttrName . "' for node (" . $strNodePath .")[" . $retIndex . "] was not found.", $flags);
                    }
                    else
                    {
                        $ret = $subNodeElement->$optPropOrAttrName;
                    }
                }
                elseif(\Scooper\isBitFlagSet($flags, C__SIMPLEHTML_FOUND_RETURN_ATTRIB ))
                {
                    if(!isset($optPropOrAttrName) || !isset($subNodeElement->attr[$optPropOrAttrName]))
                    {
                        $this->throwExceptIfRequired("Attribute '" . $optPropOrAttrName . "' for node (" . $strNodePath .")[" . $retIndex . "] was not found.", $flags);
                    }
                    else
                    {
                        $ret = $subNodeElement->attr[$optPropOrAttrName];
                    }
                }
                elseif(\Scooper\isBitFlagSet($flags, C__SIMPLEHTML_FOUND_RETURN_ALLCHILDREN))
                {
                    $ret = combineTextAllChildren($subNodeElement, false);
                    if(!isset($ret) && \Scooper\isBitFlagSet($flags, C__SIMPLEHTML_NOTFOUND_RETURN_EMPTYSTR ))
                    {
                        $ret = "";
                    }
                }
                elseif(\Scooper\isBitFlagSet($flags, C__SIMPLEHTML_FOUND_RETURN_PLAINTEXT ))
                {
                    if(!isset($subNodeElement->plaintext))
                    {
                        $this->throwExceptIfRequired("Plaintext value for node (" . $strNodePath .")[" . $retIndex . "] was not found.", $flags);
                    }
                    else
                    {
                        $ret = $subNodeElement->plaintext;
                    }
                }
            }
        } catch (\Exception $ex) {
            $strErr = $ex->getMessage();
            $this->throwExceptIfRequired($strErr, $flags);
        }

        return $ret;
    }

    function throwExceptIfRequired($strErr, $flags)
    {
        if(\Scooper\isBitFlagSet($flags, C__SIMPLEHTML_THROWEXCEPTION ))
        {
            $GLOBALS['logger']->logLine("Error getting SimpleObjectHTML node:  " . $strErr, \Scooper\C__DISPLAY_ERROR__);
            throw new ErrorException($strErr);
        }
        else
        {
            if(isDebug()) $GLOBALS['logger']->logLine("" . $strErr, \Scooper\C__DISPLAY_ITEM_DETAIL__);
        }
    }
}