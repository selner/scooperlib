<?php
require_once dirname(__FILE__) . '/../src/bootstrap.php';

use \Scooper\ScooperDataAPIWrapper;


class ScooperDataAPIWrapperTest // extends \PHPUnit_Framework_TestCase
{
    // get Redfin's page from angel.co
    private $strAPIURL = 'https://api.angel.co/1/startups/32385';
    private $strJSONKey = null;
    private $api = null;

    public function setUp($strAPI = null, $strJSONKey = null)
    {
        $this->api = new \Scooper\ScooperDataAPIWrapper();
        if(isset($strAPI )) $this->strAPIURL = $strAPI ;
        if(isset($strJSONKey )) $this->strJSONKey = $strJSONKey;

        $this->api->setVerbose(true);
    }

    public function testgetObjectsFromAPICall()
    {
        $dataAPI = $this->api->getObjectsFromAPICall($this->strAPIURL, $this->strJSONKey, \Scooper\C__API_RETURN_TYPE_ARRAY__ , null);
        assert(isset($dataAPI));

//        $this->assertInstanceOf('Psr\Log\LoggerInterface', $this->logger);
    }
}
