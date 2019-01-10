<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
use \Ufo\Core\Container;
use \Ufo\Core\Section;
use \Ufo\Modules\Controller;
 
class ControllerTest extends \Codeception\Test\Unit
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
    
    protected function getConfig()
    {
        $config = new Config();
        $config->routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $config->widgetsStorageData = require dirname(__DIR__) . '/_data/widgets.php';
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        return $config;
    }
    
    protected function getController()
    {
        $controller = new Controller();
        $controller->inject(new Container(['config' => $this->getConfig()]));
        return $controller;
    }
    
    // tests
    public function testCompose()
    {
        $section = new Section($this->getConfig()->routeStorageData['/']);
        $controller = $this->getController();
        $result = $controller->compose($section);
        $this->assertTrue(
            false !== strpos(
                $result->getView()->render(), 
                '<title>Main page</title>'
            )
        );
        
        $section = new Section($this->getConfig()->routeStorageData['/document']);
        $controller = $this->getController();
        $result = $controller->compose($section);
        $this->assertTrue(
            false !== strpos(
                $result->getView()->render(), 
                '<title>Document page</title>'
            )
        );
    }
    
    public function testComposeWidgets()
    {
        $controller = $this->getController();
        $widgets = $controller->composeWidgets([]);
        $this->assertTrue(is_array($widgets));
        $this->assertEquals(0, count($widgets));
        
        $widgets = $controller->composeWidgets($this->getConfig()->widgetsStorageData['/']);
        $this->assertEquals(2, count($widgets));
        
        $widgets = $controller->composeWidgets($this->getConfig()->widgetsStorageData['/document']);
        $this->assertEquals(3, count($widgets));
    }
}
