<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Result;
use \Ufo\Modules\Renderable;
 
class ResultTest extends \Codeception\Test\Unit
{
    protected function getResultObject($view, $headers = [])
    {
        return new class($view, $headers) extends Result {
            public $view = '';
            public $headers = [];
        };
    }
    
    // tests
    public function testResult()
    {
        $view = new Renderable('test content');
        $headers = [
            'testHeader1' => 'test header one value', 
            'testHeader2' => 'test header two value', 
        ];
        
        $result = $this->getResultObject($view, $headers);
        
        $this->assertEquals($view, $result->view);
        $this->assertEquals($headers, $result->headers);
    }
    
    public function testSetView()
    {
        $view = new Renderable('test content');
        $result = $this->getResultObject($view);
        
        $anotheView = new Renderable('another test content');
        $result->setView($anotheView);
        $this->assertEquals($anotheView, $result->view);
    }
    
    public function testGetView()
    {
        $view = new Renderable('test content');
        $result = $this->getResultObject($view);
        $this->assertEquals($view, $result->getView());
    }
    
    public function testSetHeaders()
    {
        $view = new Renderable('test content');
        $headers = [
            'testHeader1' => 'test header one value', 
            'testHeader2' => 'test header two value', 
        ];
        
        $result = $this->getResultObject($view);
        $this->assertEquals([], $result->headers);
        
        $result->setHeaders($headers);
        $this->assertEquals($headers, $result->headers);
    }
    
    public function testGetHeaders()
    {
        $view = new Renderable('test content');
        $headers = [
            'testHeader1' => 'test header one value', 
            'testHeader2' => 'test header two value', 
        ];
        $result = $this->getResultObject($view, $headers);
        $this->assertEquals($headers, $result->getHeaders());
    }
    
    public function testGetHeader()
    {
        $view = new Renderable('test content');
        $headers = [
            'testHeader1' => 'test header one value', 
            'testHeader2' => 'test header two value', 
        ];
        $result = $this->getResultObject($view, $headers);
        $this->assertEquals($headers['testHeader2'], $result->getHeader('testHeader2'));
    }
    
    public function testHasHeader()
    {
        $view = new Renderable('test content');
        $headers = [
            'testHeader1' => 'test header one value', 
            'testHeader2' => 'test header two value', 
        ];
        $result = $this->getResultObject($view, $headers);
        $this->assertFalse($result->hasHeader('testHeader0'));
        $this->assertTrue($result->hasHeader('testHeader2'));
    }
    
    public function testChangeHeader()
    {
        $view = new Renderable('test content');
        $headers = [
            'testHeader1' => 'test header one value', 
            'testHeader2' => 'test header two value', 
        ];
        
        $result = $this->getResultObject($view, $headers);
        
        $result->changeHeader('testHeader2', 'another test header two value');
        $this->assertEquals(
            [
            'testHeader1' => 'test header one value', 
            'testHeader2' => 'another test header two value', 
            ], 
            $result->headers
        );
    }
    
    public function testChangeHeaders()
    {
        $view = new Renderable('test content');
        $headers = [
            'testHeader1' => 'test header one value', 
            'testHeader2' => 'test header two value', 
        ];
        
        $result = $this->getResultObject($view, $headers);
        
        $result->changeHeaders(
            function($name, $value) {
                return $value . ' : ' . $name;
            }
        );
        
        $this->assertEquals(
            [
            'testHeader1' => 'test header one value : testHeader1', 
            'testHeader2' => 'test header two value : testHeader2', 
            ], 
            $result->headers
        );
    }
}
