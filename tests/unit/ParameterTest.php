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
    
    public function testParameterMakeWithCallback()
    {
        $parameter = Parameter::make(
            'param3', 
            'usertype', 
            'p3', 
            'get', 
            true, 
            'value-0', 
            'value-3', 
            function($value) {
                $chunks = explode('-', $value);
                if (2 > count($chunks)) {
                    return 'value-0';
                }
                return 'value-' . (string) (int) $chunks[1];
            }
        );
        $this->assertEquals($parameter->name, 'param3');
        $this->assertEquals($parameter->type, 'usertype');
        $this->assertEquals($parameter->from, 'get');
        $this->assertEquals($parameter->prefix, 'p3');
        $this->assertTrue($parameter->additional);
        $this->assertEquals($parameter->defval, 'value-0');
        $this->assertEquals($parameter->value, 'value-3');
        $this->assertNotNull($parameter->validator);

        $parameter = Parameter::make(
            'param4', 
            'usertype', 
            'p4', 
            'get', 
            true, 
            'value-0', 
            'value4', 
            function($value, $default) {
                $chunks = explode('-', $value);
                if (2 > count($chunks)) {
                    return $default;
                }
                return 'value-' . (string) (int) $chunks[1];
            }
        );
        $this->assertEquals($parameter->value, 'value-0');
    }
    
    public function testParameterMakeBool()
    {
        $parameter = Parameter::makeBool('param41', 'p41');
        $this->assertNull($parameter->value);
        
        $parameter = Parameter::makeBool('param42', 'p42', 'path', false, true, null);
        $this->assertNull($parameter->value);
        
        $parameter = Parameter::makeBool('param43', 'p43', 'path', false, true, false);
        $this->assertNotNull($parameter->value);
        $this->assertFalse($parameter->value);
        
        $parameter = Parameter::makeBool('param44', 'p44', 'path', false, false, true);
        $this->assertNotNull($parameter->value);
        $this->assertTrue($parameter->value);
    }
    
    public function testParameterMakeInt()
    {
        $parameter = Parameter::makeInt('param51', 'p51');
        $this->assertNull($parameter->value);
        
        $parameter = Parameter::makeInt('param52', 'p52', 'path', false, 0, null);
        $this->assertNull($parameter->value);
        
        $parameter = Parameter::makeInt('param53', 'p53', 'path', false, 1, 0);
        $this->assertNotNull($parameter->value);
        $this->assertEquals($parameter->value, 0);
        
        $parameter = Parameter::makeInt('param54', 'p54', 'path', false, 0, 1);
        $this->assertNotNull($parameter->value);
        $this->assertEquals($parameter->value, 1);
        
        $parameter = Parameter::makeInt('param55', 'p55', 'path', false, 0, 999999);
        $this->assertNotNull($parameter->value);
        $this->assertEquals($parameter->value, 999999);
    }
}
