<?php
require_once dirname(dirname(__FILE__)) . '/bootstrap.php';

use \Scooper\ScooperLogger;

$class = new ScooperLogger();
$class->__debug__printLine("Test", \Scooper\C__DISPLAY_ERROR__);

/**
 * Created by PhpStorm.
 * User: bryan
 * Date: 6/28/14
 * Time: 7:16 PM
 */