<?php
/**
 * Created by PhpStorm.
 * User: bryan
 * Date: 6/30/14
 * Time: 12:49 PM
 */

namespace Scooper;


class ScooperConfigTest extends \PHPUnit_Framework_TestCase {
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
 