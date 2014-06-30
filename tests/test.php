<?php
require_once dirname(dirname(__FILE__)) . '/bootstrap.php';

use \Scooper\ScooperLogger;

$class = new ScooperLogger();
$class->logLine("Test", \Scooper\C__DISPLAY_ERROR__);
