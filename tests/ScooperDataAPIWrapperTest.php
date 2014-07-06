<?php
require_once dirname(__FILE__) . '/../src/bootstrap.php';

use \Scooper\ScooperDataAPIWrapper;


class ScooperDataAPIWrapperTest // extends \PHPUnit_Framework_TestCase
{
    // get Redfin's page from angel.co
    private $strAPIURL = 'https://api.angel.co/1/startups/32385';
    private $api = null;

    public function setUp()
    {
        $this->api = new \Scooper\ScooperDataAPIWrapper();
    }

    public function testgetObjectsFromAPICall()
    {
        $dataAPI= $this->api->getObjectsFromAPICall($this->strAPIURL, null, \Scooper\C__API_RETURN_TYPE_ARRAY__ , null);

//        $this->assertInstanceOf('Psr\Log\LoggerInterface', $this->logger);
    }
}
