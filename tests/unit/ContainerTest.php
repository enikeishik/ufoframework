<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Container;
 
class ContainerTest extends \Codeception\Test\Unit
{
    /**
     * @param string $expectedExceptionClass
     * @param callable $call = null
     */
    protected function expectedException(string $expectedExceptionClass, callable $call = null)
    {
        try {
            $call();
        } catch (\Exception $e) {
            $this->assertEquals($expectedExceptionClass, get_class($e));
        }
    }
    
    // tests
    public function testContainer()
    {
        $container = new Container();
        $this->assertEquals(0, count(get_object_vars($container)));
        
        $container = new Container([
            'intProp' => 2, 
            'strProp' => 'B', 
            'blnProp' => false, 
            'arrProp' => [100, 200, 300], 
            'objProp' => (object) ['prop' => 'val'], 
        ]);
        $this->assertEquals(5, count(get_object_vars($container)));
        $this->assertEquals([100, 200, 300], $container->get('arrProp'));
        $this->assertSame([100, 200, 300], $container->get('arrProp'));
        $this->assertEquals((object) ['prop' => 'val'], $container->get('objProp'));
        $this->assertNotSame((object) ['prop' => 'val'], $container->get('objProp'));
    }
    
    public function testHasSetGetGetall()
    {
        $container = new Container();
        
        $this->assertFalse($container->has('intProp'));
        $this->assertFalse($container->has('strProp'));
        $this->assertFalse($container->has('blnProp'));
        $this->assertFalse($container->has('arrProp'));
        $this->assertFalse($container->has('objProp'));
        
        $container->set('intProp', 1);
        $container->set('strProp', 'a');
        $container->set('blnProp', true);
        $container->set('arrProp', []);
        $container->set('objProp', new stdClass());
        
        $this->assertEquals(5, count(get_object_vars($container)));
        
        $this->assertTrue($container->has('intProp'));
        $this->assertTrue($container->has('strProp'));
        $this->assertTrue($container->has('blnProp'));
        $this->assertTrue($container->has('arrProp'));
        $this->assertTrue($container->has('objProp'));
        
        $this->assertEquals(1, $container->get('intProp'));
        $this->assertEquals('a', $container->get('strProp'));
        $this->assertEquals(true, $container->get('blnProp'));
        $this->assertEquals([], $container->get('arrProp'));
        $this->assertSame([], $container->get('arrProp'));
        $this->assertEquals(new stdClass(), $container->get('objProp'));
        $this->assertNotSame(new stdClass(), $container->get('objProp'));
        
        $this->assertEquals(5, count($container->getAll()));
    }
    
    public function testGetRefForScalar()
    {
        $intVar = 3;
        $strVar = 'qwe';
        $blnVar = true;
        $container = new Container([
            'intProp' => $intVar, 
            'strProp' => $strVar, 
            'blnProp' => $blnVar, 
        ]);
        
        $int = $container->get('intProp');
        $int = 4;
        $this->assertNotEquals($int, $container->get('intProp'));
        $int =& $container->getRef('intProp');
        $int = 4;
        $this->assertEquals($int, $container->get('intProp'));
        
        $str = $container->get('strProp');
        $str = 'asd';
        $this->assertNotEquals($str, $container->get('strProp'));
        $str =& $container->getRef('strProp');
        $str = 'asd';
        $this->assertEquals($str, $container->get('strProp'));
        
        $bln = $container->get('blnProp');
        $bln = false;
        $this->assertNotEquals($bln, $container->get('blnProp'));
        $bln =& $container->getRef('blnProp');
        $bln = false;
        $this->assertEquals($bln, $container->get('blnProp'));
    }
    
    public function testGetRefForArray()
    {
        $arrVar = [100, 200, 300];
        $container = new Container([
            'arrProp' => $arrVar, 
        ]);
        
        $arr = $container->get('arrProp');
        $arr[1] = 222;
        $this->assertNotEquals($arr, $container->get('arrProp'));
        $this->assertNotSame($arr, $container->get('arrProp'));
        $this->assertEquals([100, 200, 300], $container->get('arrProp'));
        $this->assertSame([100, 200, 300], $container->get('arrProp'));
        
        $arr =& $container->getRef('arrProp');
        $arr[1] = 222;
        $this->assertEquals($arr, $container->get('arrProp'));
        $this->assertSame($arr, $container->get('arrProp'));
        $this->assertEquals([100, 222, 300], $container->get('arrProp'));
        $this->assertSame([100, 222, 300], $container->get('arrProp'));
    }
    
    public function testGetRefForObject()
    {
        $objVar = (object) ['prop' => 'val'];
        $container = new Container([
            'objProp' => $objVar, 
        ]);
        
        $obj = $container->get('objProp');
        $obj->prop = 'another';
        $this->assertEquals($obj, $container->get('objProp'));
        $this->assertEquals((object) ['prop' => 'another'], $container->get('objProp'));
        $this->assertSame($obj, $container->get('objProp'));
        
        $obj =& $container->getRef('objProp');
        $obj->prop = 'another';
        $this->assertEquals($obj, $container->get('objProp'));
        $this->assertEquals((object) ['prop' => 'another'], $container->get('objProp'));
        $this->assertSame($obj, $container->get('objProp'));
    }
    
    public function testSetByRefForScalar()
    {
        $intVar = 3;
        $strVar = 'qwe';
        $blnVar = true;
        $container = new Container();
        
        $container->set('intProp', $intVar);
        $container->set('strProp', $strVar);
        $container->set('blnProp', $blnVar);
        $intVar = 4;
        $strVar = 'asd';
        $blnVar = false;
        $this->assertNotEquals($intVar, $container->get('intProp'));
        $this->assertNotEquals($strVar, $container->get('strProp'));
        $this->assertNotEquals($blnVar, $container->get('blnProp'));
        
        $container->setByRef('intProp', $intVar);
        $container->setByRef('strProp', $strVar);
        $container->setByRef('blnProp', $blnVar);
        $intVar = 5;
        $strVar = 'zxc';
        $blnVar = true;
        $this->assertEquals($intVar, $container->get('intProp'));
        $this->assertEquals($strVar, $container->get('strProp'));
        $this->assertEquals($blnVar, $container->get('blnProp'));
    }
    
    public function testSetByRefForArray()
    {
        $container = new Container();
        
        $arrVar = [100, 200, 300];
        $container->set('arrProp', $arrVar);
        $arrVar[1] = 202;
        $this->assertNotEquals($arrVar, $container->get('arrProp'));
        $arrVar = [123];
        $this->assertNotEquals($arrVar, $container->get('arrProp'));
        
        $arrVar = [100, 200, 300];
        $container->setByRef('arrProp', $arrVar);
        $arrVar[1] = 222;
        $this->assertEquals($arrVar, $container->get('arrProp'));
        $arrVar = [];
        $this->assertEquals($arrVar, $container->get('arrProp'));
    }
    
    public function testSetByRefForObject()
    {
        $container = new Container();
        
        $objVar = (object) ['prop' => 'val'];
        $container->set('objProp', $objVar);
        $objVar->prop = 'another';
        $this->assertEquals($objVar, $container->get('objProp'));
        $objVar = new stdClass();
        $this->assertNotEquals($objVar, $container->get('objProp'));
        
        $objVar = (object) ['prop' => 'val'];
        $container->setByRef('objProp', $objVar);
        $objVar->prop = 'another';
        $this->assertEquals($objVar, $container->get('objProp'));
        $objVar = new stdClass();
        $this->assertEquals($objVar, $container->get('objProp'));
    }
}
