<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
use \Ufo\Core\Debug;
use \Ufo\Core\App;
 
class AppTest extends \Codeception\Test\Unit
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
    
    protected function getApp()
    {
        $config = new Config();
        $config->routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $config->widgetsStorageData = require dirname(__DIR__) . '/_data/widgets.php';
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        return new App($config, new Debug());
    }
    
    // tests
    public function testApp()
    {
        $app = $this->getApp();
        
        $_GET['path'] = '/section-with/callback';
        $result = $app->compose($app->parse($app->getPath()));
        $this->assertNotNull($result);
        $this->assertEquals([], $result->getHeaders());
        $this->assertNotEquals('', $result->getView()->render());
        
        $_GET['path'] = '/!document';
        $this->expectedException(
            \Ufo\Core\BadPathException::class, 
            function() use($app) { $app->execute(); }
        );
        
        $_GET['path'] = '/not2exists2path2qwe/asd';
        $this->expectedException(
            \Ufo\Core\SectionNotExistsException::class, 
            function() use($app) { $app->execute(); }
        );
        
        $_GET['path'] = '/section-disabled';
        $this->expectedException(
            \Ufo\Core\SectionDisabledException::class, 
            function() use($app) { $app->execute(); }
        );
        
        $_GET['path'] = '/module/disabled';
        $this->expectedException(
            \Ufo\Core\ModuleDisabledException::class, 
            function() use($app) { $app->execute(); }
        );
    }
    
    public function testParams()
    {
        $_GET['path'] = '/document/123/page2/rss';
        $app = $this->getApp();
        $section = $app->parse($app->getPath());
        $this->assertEquals(['123', 'page2', 'rss'], $section->params);
        
        $_GET['path'] = '/some/another/document/456/page3/yandex';
        $app = $this->getApp();
        $section = $app->parse($app->getPath());
        $this->assertEquals(['456', 'page3', 'yandex'], $section->params);
    }
    
    public function testCompose()
    {
        $_GET['path'] = '/';
        $app = $this->getApp();
        $result = $app->compose($app->parse($app->getPath()));
        $this->assertTrue(
            false !== strpos(
                $result->getView()->render(), 
                '<title>Main page</title>'
            )
        );
        $this->assertTrue(
            false !== strpos(
                $result->getView()->render(), 
                'currency widget'
            )
        );
        $this->assertTrue(
            false !== strpos(
                $result->getView()->render(), 
                'news widget'
            )
        );
        
        $_GET['path'] = '/document';
        $app = $this->getApp();
        $result = $app->compose($app->parse($app->getPath()));
        $this->assertTrue(
            false !== strpos(
                $result->getView()->render(), 
                '<title>Document page</title>'
            )
        );
        $this->assertTrue(
            false !== strpos(
                $result->getView()->render(), 
                'currency widget'
            )
        );
        $this->assertFalse(
            false !== strpos(
                $result->getView()->render(), 
                'news widget'
            )
        );
    }
}
