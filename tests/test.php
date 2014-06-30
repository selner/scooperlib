<?php
require_once dirname(__FILE__) . '/../src/bootstrap.php';
require_once dirname(__FILE__) . '/../tests/ScooperDataAPIWrapperTest.php';
require_once dirname(__FILE__) . '/../tests/ScooperSimpleCSVTest.php';


use \Scooper\ScooperLogger;
use \Scooper\ScooperDataAPIWrapper;
use \Scooper\ScooperSimpleCSV;


$class = new ScooperLogger();
$class->logLine("Test", \Scooper\C__DISPLAY_ERROR__);

$classAPI = new ScooperDataAPIWrapperTest();
$classAPI->setUp();
$classAPI->testgetObjectsFromAPICall();

$classCSV = new ScooperSimpleCSVTest();
$classCSV->setUp();
$classCSV->testwriteArrayToCSVFile();