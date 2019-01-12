<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Struct;
 
class StructTest extends \Codeception\Test\Unit
{
    // tests
    public function testStruct()
    {
        $struct = new class() extends Struct {
        };
        
        $this->assertInstanceOf(Struct::class, $struct);
    }
    
    public function testStructFromArray()
    {
        $data = [
            'fInt' => 1, 
            'fFlt' => 1.2, 
            'fStr' => 'test', 
            'fArr' => [1, 2], 
            'fObj' => new stdClass(), 
        ];
        
        $struct = new class($data) extends Struct {
            public $fInt = 0;
            public $fFlt = 0.0;
            public $fStr = '';
            public $fArr = [];
            public $fObj = null;
        };
        
        foreach ($data as $key => $val) {
            $this->assertEquals($val, $struct->$key);
        }
        
        $this->assertEquals(json_encode((object) $data), (string) $struct);
        
        $this->assertEquals($data, $struct->getArray());
        
        $this->assertEquals(array_keys($data), $struct->getFields());
    }
    
    public function testStructFromArrayWithoutCast()
    {
        $data = [
            'fInt' => 1, 
            'fFlt' => 1.2, 
            'fStr' => 'test', 
            'fArr' => [1, 2], 
            'fObj' => new stdClass(), 
        ];
        
        $struct = new class($data, false) extends Struct {
            public $fInt;
            public $fFlt;
            public $fStr;
            public $fArr;
            public $fObj;
        };
        
        foreach ($data as $key => $val) {
            $this->assertEquals($val, $struct->$key);
        }
        
        $this->assertEquals(json_encode((object) $data), (string) $struct);
        
        $this->assertEquals($data, $struct->getArray());
        
        $this->assertEquals(array_keys($data), $struct->getFields());
    }
    
    public function testStructFromStruct()
    {
        $data = [
            'fInt' => 1, 
            'fFlt' => 1.2, 
            'fStr' => 'test', 
            'fArr' => [1, 2], 
        ];
        $struct = new class($data) extends Struct {
            public $fInt = 0;
            public $fFlt = 0.0;
            public $fStr = '';
            public $fArr = [];
        };
        
        $struct2 = new class($struct) extends Struct{
            public $fInt = 0;
            public $fFlt = 0.0;
            public $fStr = '';
            public $fArr = [];
        };
        
        $this->assertNotSame($struct, $struct2);
        
        foreach (array_keys(get_object_vars($struct)) as $prop) {
            $this->assertEquals($struct->$prop, $struct2->$prop);
        }
    }
    
    public function testStructFromObject()
    {
        $data = (object) [
            'fInt' => 1, 
            'fFlt' => 1.2, 
            'fStr' => 'test', 
            'fArr' => [1, 2], 
            'fObj' => new stdClass(), 
        ];
        $struct = new class($data) extends Struct {
            public $fInt = 0;
            public $fFlt = 0.0;
            public $fStr = '';
            public $fArr = [];
            public $fObj = null;
        };
        
        $this->assertNotSame($data, $struct);
        
        foreach (array_keys(get_object_vars($data)) as $prop) {
            $this->assertEquals($data->$prop, $struct->$prop);
        }
    }
    
    public function testStructFromJson()
    {
        $data = (object) [
            'fInt' => 1, 
            'fFlt' => 1.2, 
            'fStr' => 'test', 
            'fArr' => [1, 2], 
            'fObj' => new stdClass(), 
        ];
        $struct = new class(json_encode($data)) extends Struct {
            public $fInt = 0;
            public $fFlt = 0.0;
            public $fStr = '';
            public $fArr = [];
            public $fObj = null;
        };
        
        $this->assertNotSame($data, $struct);
        
        foreach (array_keys(get_object_vars($data)) as $prop) {
            $this->assertEquals($data->$prop, $struct->$prop);
        }
    }
}
