<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Struct;
 
class StructTest extends \Codeception\Test\Unit
{
    // tests
    public function testStruct()
    {
        $data = [
            'fInt' => 1, 
            'fStr' => 'test', 
            'fArr' => [1, 2], 
            'fObj' => new stdClass(), 
        ];
        $struct = new class($data) extends Struct {
            public $fInt;
            public $fStr;
            public $fArr;
            public $fObj;
        };
        
        $this->assertEquals($data['fInt'], $struct->fInt);
        $this->assertEquals($data['fStr'], $struct->fStr);
        $this->assertEquals($data['fArr'], $struct->fArr);
        $this->assertEquals($data['fObj'], $struct->fObj);
        $this->assertEquals(json_encode((object) $data), (string) $struct);
        $this->assertEquals($data, $struct->getValues());
        $this->assertEquals(array_keys($data), $struct->getFields());
    }
}
