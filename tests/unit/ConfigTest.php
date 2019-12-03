<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
 
class ConfigTest extends \Codeception\Test\Unit
{
    public function testConfig()
    {
        $config = new Config();
        $this->assertFalse(property_exists($config, 'test'));
        
        $config = new Config(['test' => 3]);
        $this->assertTrue(property_exists($config, 'test'));
        $this->assertEquals($config->test, 3);
    }
}
