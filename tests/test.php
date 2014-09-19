<?php
require_once dirname(__FILE__) . '/../src/bootstrap.php';
require_once dirname(__FILE__) . '/../tests/testsHelpers.php';

require_once dirname(__FILE__) . '/../tests/ScooperDataAPIWrapperTest.php';
require_once dirname(__FILE__) . '/../tests/ScooperSimpleCSVTest.php';


use \Scooper\ScooperLogger;
use \Scooper\ScooperDataAPIWrapper;
use \Scooper\ScooperSimpleCSV;

testAllHelpers();

$class = new ScooperLogger();
$class->logLine("Test", \Scooper\C__DISPLAY_ERROR__);



$simpHTMLObj = new \Scooper\SimpleHTMLHelper("http://www.payscale.com");
var_dump($simpHTMLObj->getDownloadTime());
$simpHTMLObj = new \Scooper\SimpleHTMLHelper(\SimpleHtmlDom\file_get_html("http://www.bryanselner.com"));
$node = $simpHTMLObj->getSimpleHtmlDOMNodeObject();
$body = $node->find("body");
assert(isset($body));
// $simpHTMLObj = new \Scooper\SimpleHTMLHelper(\SimpleHtmlDom\file_get_html("http://BADURL.bryanselner.com"));



$classAPI = new ScooperDataAPIWrapperTest();
$classAPI->setUp('https://api.angel.co/1/startups/32385', null);
$classAPI->testgetObjectsFromAPICall();


$classAPI = new ScooperDataAPIWrapperTest();
$classAPI->setUp('https://api.angel.co/1/startups/32385', 'locations');
$classAPI->testgetObjectsFromAPICall();

$classCSV = new ScooperSimpleCSVTest();
$classCSV->setUp();
$classCSV->testWriteArrayToFile("csv");
$classCSV->testWriteArrayToFile("html");
