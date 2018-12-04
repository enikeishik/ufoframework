<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Debug;
 
class DebugTest extends \Codeception\Test\Unit
{
    /**
     * @var int
     */
    protected const TIME_PRECISION = 4;
    
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
        
        $this->assertEquals(-1, $debug->trace());
        $this->assertEquals(0, $debug->trace('first trace'));
        $this->assertEquals(-1, $debug->trace(0));
        $this->assertEquals(1, $debug->trace('second trace'));
        $this->assertEquals(-1, $debug->trace(1));
        $this->assertEquals(2, $debug->getTraceCounter());
        
        $this->assertEquals(2, $debug->trace('third trace'));
        $this->assertEquals(3, $debug->getTraceCounter());
        
        $trace = $debug->getTrace();
        $this->assertTrue(is_array($trace));
        $traceCount = count($trace);
        $traceFirstItem = $trace[0];
        $traceLastItem = $trace[$traceCount - 1];
        $this->assertEquals($debug->getTraceCounter(), $traceCount);
        $this->assertTrue(array_key_exists('result', $traceFirstItem));
        $this->assertTrue(array_key_exists('result', $traceLastItem));
        $this->assertEquals('OK', $traceFirstItem['result']);
        $this->assertEquals('', $traceLastItem['result']);
        
        $debug->traceEnd();
        $trace = $debug->getTrace();
        $this->assertTrue(is_array($trace));
        $this->assertEquals($traceCount, count($trace));
        $traceLastItem = $trace[$traceCount - 1];
        $this->assertEquals('Trace end', $traceLastItem['result']);
    }
}
