<?php
require_once dirname(__FILE__) . '/../src/bootstrap.php';

use \Scooper\ScooperLogger;

$class = new ScooperLogger();
$class->logLine("Test", \Scooper\C__DISPLAY_ERROR__);
