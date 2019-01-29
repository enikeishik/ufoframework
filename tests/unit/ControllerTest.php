<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
use \Ufo\Core\Container;
use \Ufo\Core\ModuleParameterConflictException;
use \Ufo\Core\ModuleParameterFormatException;
use \Ufo\Core\ModuleParameterUnknownException;
use \Ufo\Core\Section;
use \Ufo\Modules\Controller;
use \Ufo\Modules\Model;
use \Ufo\Modules\ModelInterface;
use \Ufo\Modules\Parameter;
use \Ufo\Modules\View;
use \Ufo\Widgets\WidgetsArrayStorage;
 
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
    
    public function testSetData()
    {
        $controller = new class() extends Controller {
            public $data = [];
            
            public function setData(Section $section = null): void
            {
                parent::setData($section);
            }
            
            protected function getModel(): ModelInterface
            {
                $model = new class() extends Model {
                    public function getItems()
                    {
                        return [];
                    }
                };
                $model->inject($this->container);
                return $model;
            }
        };
        $controller->inject(new Container(['config' => $this->getConfig()]));
        
        $this->assertEquals([], $controller->data);
        $controller->setData();
        $this->assertEquals(['items' => [], 'section' => null], $controller->data);
    }
    
    public function testComposeWidgets()
    {
        $controller = $this->getController();
        $widgets = $controller->composeWidgets([]);
        $this->assertTrue(is_array($widgets));
        $this->assertCount(0, $widgets);
        
        $widgets = 
            $controller->composeWidgets(
                (new WidgetsArrayStorage($this->getConfig()->widgetsStorageData))
                ->getWidgets(new Section(['path' => '/']))
            );
        $this->assertCount(2, $widgets);
        $this->assertArrayHasKey('left col top', $widgets);
        $this->assertArrayHasKey('right col bottom', $widgets);
        $this->assertInstanceOf(View::class, $widgets['left col top']);
        $this->assertInstanceOf(View::class, $widgets['right col bottom']);
        
        $widgets = 
            $controller->composeWidgets(
                (new WidgetsArrayStorage($this->getConfig()->widgetsStorageData))
                ->getWidgets(new Section(['path' => '/document']))
            );
        $this->assertCount(3, $widgets);
        
        $controller = new Controller();
        $config = $this->getConfig();
        $config->widgetsStorageData['/document']['middle col top'][] = 
            ['vendor' => 'somevendor', 'module' => '', 'name' => 'somewidget', 'title' => 'some vendor widget', 'text' => 'some vendor wdg text'];
        $config->widgetsStorageData['/document']['middle col top'][] = 
            ['vendor' => '', 'module' => '', 'name' => '', 'title' => 'bad widget', 'text' => 'bad wdg text'];
        $controller->inject(new Container(['config' => $config]));
$tpl = <<<EOD
namespace Ufo\Modules\Somevendor\Widgets\Somewidget;
class Controller extends \Ufo\Core\DIObject
{
    public function compose()
    {
        return new \Ufo\Core\Result(new \Ufo\Modules\Renderable('some vendor widget content'));
    }
}
EOD;
        eval($tpl);
        $widgets = 
            $controller->composeWidgets(
                (new WidgetsArrayStorage($config->widgetsStorageData))
                ->getWidgets(new Section(['path' => '/document']))
            );
        $this->assertCount(4, $widgets);
    }
    
    protected function getControllerForParamsFromPath()
    {
        return new class() extends Controller {
            public $params = [];
            public $paramsAssigned = [];
            public function setParamsFromPath(array $pathParams): void
            {
                parent::setParamsFromPath($pathParams);
            }
        };
    }
    
    public function testParamsFromPathByOne()
    {
        $controller = $this->getControllerForParamsFromPath();
        
        $paramName = 'test01';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'bool', 'test01', 'path', false, false);
        $this->assertNull($controller->params[$paramName]->value);
        $controller->setParamsFromPath(['test01']);
        $this->assertTrue($controller->params[$paramName]->value);
        
        $paramName = 'test02';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'int', 'intprefix', 'path', false, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $controller->setParamsFromPath(['intprefix123']);
        $this->assertEquals(123, $controller->params[$paramName]->value);
        
        $paramName = 'test03';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'int', '', 'path', false, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $controller->setParamsFromPath(['456']);
        $this->assertEquals(456, $controller->params[$paramName]->value);
        
        $paramName = 'test04';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'date', 'dateprefix', 'path', false, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $controller->setParamsFromPath(['dateprefix1970-02-03']);
        $this->assertEquals(
            strtotime('1970-02-03'), 
            $controller->params[$paramName]->value
        );
        
        $paramName = 'test05';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'date', '', 'path', false, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $controller->setParamsFromPath(['1970-02-03']);
        $this->assertEquals(
            strtotime('1970-02-03'), 
            $controller->params[$paramName]->value
        );
        
        $paramName = 'test06';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'string', 'strprefix', 'path', false, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $controller->setParamsFromPath(['strprefixval456']);
        $this->assertEquals('val456', $controller->params[$paramName]->value);
        
        $paramName = 'test07';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'string', '', 'path', false, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $controller->setParamsFromPath(['val789']);
        $this->assertEquals('val789', $controller->params[$paramName]->value);
        
        $paramName = 'test08';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'int', '', 'path', false, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $this->expectedException(
            \Ufo\Core\ModuleParameterUnknownException::class, 
            function() use($controller) { $controller->setParamsFromPath(['asd']); }
        );
        
        $paramName = 'test09';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'int', '', 'path', true, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $this->expectedException(
            \Ufo\Core\ModuleParameterUnknownException::class, 
            function() use($controller) { $controller->setParamsFromPath(['asd']); }
        );
        
        $paramName = 'test10';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'date', '', 'path', true, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $this->expectedException(
            \Ufo\Core\ModuleParameterUnknownException::class, 
            function() use($controller) { $controller->setParamsFromPath(['1970-01-aa']); }
        );
        
        $paramName = 'test11';
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params[$paramName] = Parameter::make($paramName, 'date', 'dt', 'path', true, 0);
        $this->assertNull($controller->params[$paramName]->value);
        $this->expectedException(
            \Ufo\Core\ModuleParameterFormatException::class, 
            function() use($controller) { $controller->setParamsFromPath(['dt1970-01-aa']); }
        );
    }
    
    public function testParamsFromPathByMany()
    {
        $controller = $this->getControllerForParamsFromPath();
        
        
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->params['test01'] = Parameter::make('test01', 'bool', 'test01', 'path', true, false);
        $controller->params['test02'] = Parameter::make('test02', 'int', '', 'path', false, 0);
        $controller->params['test03'] = Parameter::make('test03', 'string', '', 'path', true, '');
        $this->assertNull($controller->params['test01']->value);
        $this->assertNull($controller->params['test02']->value);
        $this->assertNull($controller->params['test03']->value);
        $controller->setParamsFromPath(['test01', '789', 'qwe']);
        $this->assertTrue($controller->params['test01']->value);
        $this->assertEquals(789, $controller->params['test02']->value);
        $this->assertEquals('qwe', $controller->params['test03']->value);
        
        
        $controller->params = [];
        $controller->params['test11'] = Parameter::make('test11', 'bool', 'test11', 'path', true, false);
        $controller->params['test12'] = Parameter::make('test12', 'int', '', 'path', false, 0);
        $controller->params['test13'] = Parameter::make('test13', 'date', 'dt', 'path', false, 0);
        $controller->params['test14'] = Parameter::make('test14', 'string', 'prefix', 'path', false, '');
        $controller->params['test15'] = Parameter::make('test15', 'string', '', 'path', false, '');
        $controller->params['test16'] = Parameter::make('test16', 'date', '', 'path', false, 0);
        $this->assertNull($controller->params['test11']->value);
        $this->assertNull($controller->params['test12']->value);
        $this->assertNull($controller->params['test13']->value);
        $this->assertNull($controller->params['test14']->value);
        $this->assertNull($controller->params['test15']->value);
        $this->assertNull($controller->params['test16']->value);
        
        $controller->paramsAssigned = [];
        $controller->setParamsFromPath(['789']);
        $this->assertEquals(789, $controller->params['test12']->value);
        
        $controller->paramsAssigned = [];
        $controller->setParamsFromPath(['dt1970-02-03']);
        $this->assertEquals(strtotime('1970-02-03'), $controller->params['test13']->value);
        
        $controller->paramsAssigned = [];
        $controller->setParamsFromPath(['prefixQWE']);
        $this->assertEquals('QWE', $controller->params['test14']->value);
        
        $controller->paramsAssigned = [];
        $controller->setParamsFromPath(['strparam-without-prefix']);
        $this->assertEquals('strparam-without-prefix', $controller->params['test15']->value);
        
        $controller->paramsAssigned = [];
        $this->expectedException(
            \Ufo\Core\ModuleParameterUnknownException::class, 
            function() use($controller) { $controller->setParamsFromPath(['QWE']); }
        );
        
        $controller->paramsAssigned = [];
        $this->expectedException(
            \Ufo\Core\ModuleParameterFormatException::class, 
            function() use($controller) { $controller->setParamsFromPath(['dtasd']); }
        );
        
        $controller->paramsAssigned = [];
        $this->expectedException(
            \Ufo\Core\ModuleParameterConflictException::class, 
            function() use($controller) { $controller->setParamsFromPath([123, 'prefixQWE']); }
        );
        
        $controller->paramsAssigned = [];
        $this->expectedException(
            \Ufo\Core\ModuleParameterConflictException::class, 
            function() use($controller) { $controller->setParamsFromPath([123, '456']); }
        );
        
        $controller->paramsAssigned = [];
        $this->expectedException(
            \Ufo\Core\ModuleParameterConflictException::class, 
            function() use($controller) { $controller->setParamsFromPath([123, '1970-01-01']); }
        );
        
        $controller->paramsAssigned = [];
        $controller->params['test15']->value = null;
        $this->expectedException(
            \Ufo\Core\ModuleParameterConflictException::class, 
            function() use($controller) { $controller->setParamsFromPath([123, 'strparam-without-prefix']); }
        );
    }
    
    protected function getControllerForSetParams()
    {
        return new class() extends Controller {
            public $params = [];
            public $paramsAssigned = [];
            public function initParams(): void
            {
                parent::initParams();
            }
            public function setParams(array $pathParams): void
            {
                parent::setParams($pathParams);
            }
        };
    }
    
    public function testSetParams()
    {
        $controller = $this->getControllerForSetParams();
        
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->initParams();
        $this->assertNull($controller->params['isRoot']->value);
        $controller->setParams([]);
        $this->assertTrue($controller->params['isRoot']->value);
        
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->initParams();
        $this->assertNull($controller->params['isRoot']->value);
        $controller->setParams(['123', 'page2']);
        $this->assertFalse($controller->params['isRoot']->value);
        $this->assertEquals(123, $controller->params['itemId']->value);
        $this->assertEquals(2, $controller->params['page']->value);
        $this->assertFalse($controller->params['isRss']->value);
        
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->initParams();
        $controller->params['somegetintparam'] = Parameter::make('somegetintparam', 'int', 'somegetintparam', 'get', false, 0);
        $controller->params['somegetblnparam'] = Parameter::make('somegetblnparam', 'bool', 'somegetblnparam', 'get', true, false);
        $controller->params['somegetstrparam'] = Parameter::make('somegetstrparam', 'string', 'somegetstrparam', 'get', true, '');
        $_GET['somegetintparam'] = 5;
        $_GET['somegetblnparam'] = 1;
        $_GET['somegetstrparam'] = 'str-value';
        $controller->initParams(); //to ensure reinit not work
        $this->assertNull($controller->params['isRoot']->value);
        $this->assertNull($controller->params['isRss']->value);
        $this->assertEquals(0, $controller->params['somegetintparam']->value);
        $this->assertEquals(false, $controller->params['somegetblnparam']->value);
        $this->assertEquals('', $controller->params['somegetstrparam']->value);
        $controller->setParams([]);
        $this->assertEquals(5, $controller->params['somegetintparam']->value);
        $this->assertTrue($controller->params['somegetblnparam']->value);
        $this->assertEquals('str-value', $controller->params['somegetstrparam']->value);
        $this->assertFalse($controller->params['isRoot']->value);
        $this->assertFalse($controller->params['isRss']->value);
        $_GET['somegetintparam'] = 10;
        $controller->setParams([]); //to ensure reset not work
        $this->assertEquals(5, $controller->params['somegetintparam']->value);
        $this->assertTrue($controller->params['somegetblnparam']->value);
        $this->assertEquals('str-value', $controller->params['somegetstrparam']->value);
        
        $controller->params = [];
        $controller->paramsAssigned = [];
        $controller->initParams();
        $this->assertNull($controller->params['isRoot']->value);
        $this->assertNull($controller->params['isRss']->value);
        $controller->setParams(['rss']);
        $this->assertFalse($controller->params['isRoot']->value);
        $this->assertTrue($controller->params['isRss']->value);
    }
}
