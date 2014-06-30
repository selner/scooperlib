<?php
require_once dirname(__FILE__) . '/../src/bootstrap.php';

use \Scooper\ScooperConfig;

class ScooperSimpleCSVTest // extends \PHPUnit_Framework_TestCase
{
    private $class = null;

    public function setUp()
    {
        $this->class = new \Scooper\ScooperConfig(dirname(__FILE__) . '/../examples/example_config.ini');
    }

    public function testwriteArrayToCSVFile()
    {
        $this->class->printAllSettings();

        // $this->assertInstanceOf('Psr\Log\LoggerInterface', $this->logger);
    }
}
