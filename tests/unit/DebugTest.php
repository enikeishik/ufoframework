<?php
use \Ufo\Core\Debug;
use \Ufo\Core\DebugIndexNotExistsException;
 
class DebugTest extends BaseUnitTest
{
    /**
     * @var int
     */
    protected const TIME_PRECISION = 2;
    
    // tests
    public function testGetExecutionTime()
    {
        $debug = new Debug();
        $startTime = microtime(true);
        
        usleep(10 * 1000);
        $result = $debug->getExecutionTime();
        $expected = microtime(true) - $startTime;
        
        $this->assertEquals(
            round($expected, self::TIME_PRECISION), 
            round($result, self::TIME_PRECISION)
        );
    }
    
    public function testTrace()
    {
        $debug = new Debug();
        
        // not work correctly
        // $this->expectException(
            // DebugIndexNotExistsException::class, 
            // function() use($debug) {
                // $debug->traceClose();
            // }
        // );
        $this->expectedException(
            DebugIndexNotExistsException::class, 
            function() use($debug) {
                $debug->traceClose();
            }
        );
        $this->assertEquals(0, $debug->trace('first trace'));
        $this->assertNull($debug->traceClose(0));
        $this->assertEquals(1, $debug->trace('second trace'));
        $this->assertNull($debug->traceClose(1));
        $this->assertEquals(2, $debug->getTraceCount());
        
        $this->assertEquals(2, $debug->trace('third trace'));
        $this->assertEquals(3, $debug->getTraceCount());
        
        $trace = $debug->getTrace();
        $this->assertTrue(is_array($trace));
        $traceCount = count($trace);
        $traceFirstItem = $trace[0];
        $traceLastItem = $trace[$traceCount - 1];
        $this->assertEquals($debug->getTraceCount(), $traceCount);
        $this->assertTrue(array_key_exists('result', $traceFirstItem));
        $this->assertTrue(array_key_exists('result', $traceLastItem));
        $this->assertEquals('OK', $traceFirstItem['result']);
        $this->assertEquals('', $traceLastItem['result']);
        
        $this->assertNull($debug->traceEnd());
        $trace = $debug->getTrace();
        $this->assertTrue(is_array($trace));
        $this->assertEquals($traceCount, count($trace));
        $traceLastItem = $trace[$traceCount - 1];
        $this->assertEquals('Trace end', $traceLastItem['result']);
    }
    
    public function testErrorHandler()
    {
        set_error_handler(['\Ufo\Core\Debug', 'errorHandler']);
        $a = 1 / 0;
        restore_error_handler();
        $debug = new Debug();
        $errors = $debug->getErrors();
        $this->assertTrue(is_array($errors));
        $this->assertCount(1, $errors);
        $this->assertContains('Division by zero', $errors[0]);
    }
    
    public function testVarDump()
    {
        ob_start();
        Debug::vd('test', false, false);
        $content = ob_get_clean();
        $this->assertContains('test', $content);
    }
}
