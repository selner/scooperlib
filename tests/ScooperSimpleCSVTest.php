<?php
require_once dirname(__FILE__) . '/../src/bootstrap.php';


class ScooperSimpleCSVTest // extends \PHPUnit_Framework_TestCase
{
    private $arrTest = null;

    public function setUp()
    {
        $GLOBALS['logger'] = new \Scooper\ScooperLogger(sys_get_temp_dir());

        $this->arrTest = array(array("one", "two"));
    }

    public function testWriteArrayToFile($strExt)
    {
        $strOutFile = sys_get_temp_dir(). "/ScooperSimpleCSVTest.".$strExt;
        $class = new \Scooper\ScooperSimpleCSV($strOutFile, 'w');
        print("Writing array to " . $strOutFile .PHP_EOL);
        switch(strtolower($strExt))
        {

            case "csv":
                $class->writeArrayToCSVFile($this->arrTest);
                break;

            case "html":
            case "htm":
            $class->writeArrayToHTMLFile($this->arrTest);
                break;
        }

    }

}
