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
        $this->class = new \Scooper\ScooperConfig(dirname(__FILE__) . '/../examples/example_config.ini');
    }


}
 