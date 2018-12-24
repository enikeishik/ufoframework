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
        $this->assertFalse(property_exists($config, 'test'));
        
        $config = new class(['test' => 3]) extends Config { public $test; };
        $this->assertTrue(property_exists($config, 'test'));
        $this->assertEquals($config->test, 3);
    }
    
    public function testLoadArray()
    {
        $config = new Config();
        $this->assertFalse(property_exists($config, 'test'));
        
        $config->loadArray(['test' => 4]);
        $this->assertTrue(property_exists($config, 'test'));
        $this->assertEquals($config->test, 4);
        
        $config->loadArray(['test' => 5]);
        $this->assertEquals($config->test, 4);
        
        $config->loadArray(['test' => 5], true);
        $this->assertEquals($config->test, 5);
    }
    
    public function testLoadFromIni()
    {
        $config = new Config();
        $config->loadFromIni(dirname(__DIR__) . '/_data/config.ini');
        $this->assertTrue(property_exists($config, 'test1'));
        $this->assertTrue(property_exists($config, 'testVarName1'));
        $this->assertEquals($config->test1, 7);
        $this->assertEquals($config->testVarName1, 'test var name 1');
        $this->assertEquals($config->typedVal1, 1);
        $this->assertFalse($config->typedVal1 === '1');
        $this->assertTrue($config->typedVal1 === 1);
        $this->assertEquals($config->typedValOn, true);
        $this->assertEquals($config->typedValYes, true);
        $this->assertEquals($config->typedValOff, false);
        $this->assertEquals($config->typedValNo, false);
        $this->assertTrue($config->typedVal15 === 1.5);
        $this->assertTrue($config->typedValStr === 'str');
        $this->assertTrue($config->typedValStrnum === '123');
        $this->assertTrue($config->typedValNum === 123);
    }
}
