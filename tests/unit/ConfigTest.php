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
    public function testLoad()
    {
        $config = new Config();
        $initialConfigPropsCount = count(get_object_vars($config));
        
        $config->load(dirname(__DIR__) . '/_data/non-existence-config.php');
        $this->assertCount($initialConfigPropsCount, get_object_vars($config));
        
        $config->load(dirname(__DIR__) . '/_data/config-bad.php');
        $this->assertCount($initialConfigPropsCount, get_object_vars($config));
        
        $config->load(dirname(__DIR__) . '/_data/config-arr.php');
        $this->assertNotEquals($initialConfigPropsCount, get_object_vars($config));
        $this->assertTrue(property_exists($config, 'testArr1'));
        $this->assertEquals('test array config value 1', $config->testArr1);
        
        $config = new Config();
        $initialConfigPropsCount = count(get_object_vars($config));
        $config->load(dirname(__DIR__) . '/_data/config-obj.php');
        $this->assertNotEquals($initialConfigPropsCount, get_object_vars($config));
        $this->assertTrue(property_exists($config, 'testObj1'));
        $this->assertEquals('test object config value 1', $config->testObj1);
    }
    
    public function testLoadWithDefault()
    {
        $config = new Config();
        $initialConfigPropsCount = count(get_object_vars($config));
        
        $nonExistenceConfig = dirname(__DIR__) . '/_data/non-existence-config.php';
        $badConfig = dirname(__DIR__) . '/_data/config-bad.php';
        $arrConfig = dirname(__DIR__) . '/_data/config-arr.php';
        $arrConfigDefault = dirname(__DIR__) . '/_data/config-arr-default.php';
        
        $config->loadWithDefault($nonExistenceConfig, $nonExistenceConfig);
        $this->assertCount($initialConfigPropsCount, get_object_vars($config));
        
        $config->loadWithDefault($badConfig, $badConfig);
        $this->assertCount($initialConfigPropsCount, get_object_vars($config));
        
        $config->loadWithDefault($arrConfig, $arrConfigDefault);
        $this->assertNotEquals($initialConfigPropsCount, get_object_vars($config));
        $this->assertTrue(property_exists($config, 'testArr1'));
        $this->assertEquals('test array config value 1', $config->testArr1);
        $this->assertTrue(property_exists($config, 'testArr2'));
        $this->assertEquals('test default array config value 2', $config->testArr2);
    }
    
    public function testLoadArray()
    {
        $config = new Config();
        $initialConfigPropsCount = count(get_object_vars($config));
        $this->assertFalse(property_exists($config, 'test'));
        
        $config->loadArray(['test' => 4]);
        $this->assertNotEquals($initialConfigPropsCount, get_object_vars($config));
        $this->assertTrue(property_exists($config, 'test'));
        $this->assertEquals(4, $config->test);
        
        $config->loadArray(['test' => 5]);
        $this->assertEquals(4, $config->test);
        
        $config->loadArray(['test' => 5], true);
        $this->assertEquals(5, $config->test);
    }
    
    public function testLoadFromIni()
    {
        $config = new Config();
        $initialConfigPropsCount = count(get_object_vars($config));
        
        $config->loadFromIni(dirname(__DIR__) . '/_data/non-existence-config.ini');
        $this->assertCount($initialConfigPropsCount, get_object_vars($config));
        
        $config->loadFromIni(dirname(__DIR__) . '/_data/config-bad.ini');
        $this->assertCount($initialConfigPropsCount, get_object_vars($config));
        
        $config->loadFromIni(dirname(__DIR__) . '/_data/config.ini');
        $this->assertNotEquals($initialConfigPropsCount, get_object_vars($config));
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
