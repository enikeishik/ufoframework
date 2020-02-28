<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Modules\Parameter;
 
class ParameterTest extends \Codeception\Test\Unit
{
    public function testParameterDefaults()
    {
        $parameter = new Parameter();
        
        $this->assertTrue(property_exists($parameter, 'name'));
        $this->assertEquals($parameter->name, '');
        
        $this->assertTrue(property_exists($parameter, 'type'));
        $this->assertEquals($parameter->type, '');
        
        $this->assertTrue(property_exists($parameter, 'from'));
        $this->assertEquals($parameter->from, '');
        
        $this->assertTrue(property_exists($parameter, 'prefix'));
        $this->assertEquals($parameter->prefix, '');
        
        $this->assertTrue(property_exists($parameter, 'additional'));
        $this->assertFalse($parameter->additional);
        
        $this->assertTrue(property_exists($parameter, 'defval'));
        $this->assertNull($parameter->defval);
        
        $this->assertTrue(property_exists($parameter, 'value'));
        $this->assertNull($parameter->value);
        
        $this->assertTrue(property_exists($parameter, 'validator'));
        $this->assertNull($parameter->validator);
    }
    
    public function testParameterMakeDefault()
    {
        $parameter = Parameter::make('param1', 'int', 'p1');
        $this->assertEquals($parameter->name, 'param1');
        $this->assertEquals($parameter->type, 'int');
        $this->assertEquals($parameter->from, 'path');
        $this->assertEquals($parameter->prefix, 'p1');
        $this->assertFalse($parameter->additional);
        $this->assertNull($parameter->defval);
        $this->assertNull($parameter->value);
        $this->assertNull($parameter->validator);
    }
    
    public function testParameterMakeWithValues()
    {
        $parameter = Parameter::make('param2', 'int', 'p2', 'get', true, 0, 2);
        $this->assertEquals($parameter->name, 'param2');
        $this->assertEquals($parameter->type, 'int');
        $this->assertEquals($parameter->from, 'get');
        $this->assertEquals($parameter->prefix, 'p2');
        $this->assertTrue($parameter->additional);
        $this->assertEquals($parameter->defval, 0);
        $this->assertEquals($parameter->value, 2);
        $this->assertNull($parameter->validator);
    }
}
