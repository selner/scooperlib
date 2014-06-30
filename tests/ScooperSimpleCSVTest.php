<?php
require_once dirname(__FILE__) . '/../src/bootstrap.php';

use \Scooper\ScooperSimpleCSV;

class ScooperSimpleCSVTest // extends PHPUnit_Framework_TestCase {
{
    private $class = null;

    public function setUp()
    {
        $path = sys_get_temp_dir();
        $this->class = new \Scooper\ScooperSimpleCSV($path . "/ScooperSimpleCSVTest.csv", 'w');
    }

    public function testwriteArrayToCSVFile()
    {
        $this->class->writeArrayToCSVFile(array(array("one", "two")));

        // $this->assertInstanceOf('Psr\Log\LoggerInterface', $this->logger);
    }
}
