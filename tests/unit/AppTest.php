<?php
use \Ufo\Core\Config;
use \Ufo\Core\Debug;
use \Ufo\Core\App;
use \Ufo\Core\Result;
use \Ufo\Core\Section;
use \Ufo\Modules\Renderable;
 
class AppTest extends BaseUnitTest
{
    protected function getApp($withDebug = true)
    {
        $config = new Config();
        $config->routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $config->widgetsStorageData = require dirname(__DIR__) . '/_data/widgets.php';
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        return new App($config, $withDebug ? new Debug() : null);
    }
    
    // tests
    public function testApp()
    {
        $app = $this->getApp();
        $this->assertInstanceOf(App::class, $app);
    }
    
    protected function assertContentContains($needle)
    {
        $app = $this->getApp();
        ob_start();
        $app->execute();
        $content = ob_get_clean();
        $this->assertContains($needle, $content);
    }
    
    public function testExecute()
    {
        $this->assertContentContains('<title>Main page</title>');
        
        $_GET['path'] = '/!document';
        $this->assertContentContains('Bad path');
        
        $_GET['path'] = '/not2exists2path2qwe/asd';
        $this->assertContentContains('Section not exists');
        
        $_GET['path'] = '/section-disabled';
        $this->assertContentContains('Section disabled');
        
        $_GET['path'] = '/module/disabled';
        $this->assertContentContains('Section module disabled');
        
        $_GET['path'] = '/document/123/rss';
        $this->assertContentContains('Module parameter conflict with another');
        
        $_GET['path'] = '/document/123/asd';
        $this->assertContentContains('Module parameter unknown');
    }
    
    public function testGetPath()
    {
        $app = $this->getApp(false);
        $this->assertEquals('/', $app->getPath());
        
        $_GET['path'] = '/document';
        $app = $this->getApp(false);
        $this->assertEquals($_GET['path'], $app->getPath());
        
        $_GET['path'] = '/!document';
        $app = $this->getApp(false);
        $this->expectedException(
            \Ufo\Core\BadPathException::class, 
            function() use($app) { $app->getPath(); }
        );
    }
    
    public function testCompose()
    {
        $app = $this->getApp();
        $_GET['path'] = '/section-with/callback';
        $result = $app->compose($app->parse($app->getPath()));
        $this->assertNotNull($result);
        $this->assertEquals([], $result->getHeaders());
        $this->assertNotEquals('', $result->getView()->render());
        $this->assertEquals($_GET['path'], $app->getPath());
        
        $app = $this->getApp();
        $_GET['path'] = '/!document';
        $this->expectedException(
            \Ufo\Core\BadPathException::class, 
            function() use($app) { $result = $app->compose($app->parse($app->getPath())); }
        );
        
        $app = $this->getApp();
        $_GET['path'] = '/not2exists2path2qwe/asd';
        $this->expectedException(
            \Ufo\Core\SectionNotExistsException::class, 
            function() use($app) { $result = $app->compose($app->parse($app->getPath())); }
        );
        
        $app = $this->getApp();
        $_GET['path'] = '/section-disabled';
        $this->expectedException(
            \Ufo\Core\SectionDisabledException::class, 
            function() use($app) { $result = $app->compose($app->parse($app->getPath())); }
        );
        
        $app = $this->getApp();
        $_GET['path'] = '/module/disabled';
        $this->expectedException(
            \Ufo\Core\ModuleDisabledException::class, 
            function() use($app) { $result = $app->compose($app->parse($app->getPath())); }
        );
        
        $app = $this->getApp();
        $_GET['path'] = '/document/123/rss';
        $this->expectedException(
            \Ufo\Core\ModuleParameterConflictException::class, 
            function() use($app) { $result = $app->compose($app->parse($app->getPath())); }
        );
        
        $app = $this->getApp();
        $_GET['path'] = '/document/123/asd';
        $this->expectedException(
            \Ufo\Core\ModuleParameterUnknownException::class, 
            function() use($app) { $result = $app->compose($app->parse($app->getPath())); }
        );
    }
    
    public function testComposeCallback()
    {
        $app = $this->getApp();
        $result = $app->composeCallback(
            function() { return 'some content'; },
            new Section()
        );
        $this->assertInstanceOf(Result::class, $result);
        $content = $result->getView()->render();
        $this->assertContains('some content', $content);
        
        $app = $this->getApp();
        $result = $app->composeCallback(
            function() { return new Result(new Renderable('some content')); },
            new Section()
        );
        $this->assertInstanceOf(Result::class, $result);
        $content = $result->getView()->render();
        $this->assertContains('some content', $content);
    }
    
    public function testGetError()
    {
        $app = $this->getApp();
        
        $err = $app->getError();
        $this->assertTrue($err instanceof Result);
        $this->assertTrue($err->getView() instanceof Renderable);
        $this->assertTrue(is_array($err->getHeaders()));
        $this->assertEquals(1, count($err->getHeaders()));
        $this->assertTrue(false !== strpos($err->getHeaders()[0], '200 OK'));
        
        $err = $app->getError(404, 'Not found');
        $this->assertTrue(false !== strpos($err->getHeaders()[0], '404 Not found'));
        
        $err = $app->getError(301, 'Moved Permanently', ['location' => '/']);
        $this->assertEquals(2, count($err->getHeaders()));
        $this->assertTrue(false !== strpos($err->getHeaders()[0], '301 Moved Permanently'));
        $this->assertEquals('Location: http://localhost/', $err->getHeaders()[1]);
        
        $err = $app->getError(301, 'Moved Permanently', ['location' => 'http://localhost/']);
        $this->assertEquals(2, count($err->getHeaders()));
        $this->assertTrue(false !== strpos($err->getHeaders()[0], '301 Moved Permanently'));
        $this->assertEquals('Location: http://localhost/', $err->getHeaders()[1]);
    }
    
    public function testSectionParams()
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
    
    public function testWidgets()
    {
        $_GET['path'] = '/';
        $app = $this->getApp();
        $result = $app->compose($app->parse($app->getPath()));
        $this->assertContains(
            '<title>Main page</title>', 
            $result->getView()->render()
        );
        $this->assertContains(
            'currency widget', 
            $result->getView()->render()
        );
        $this->assertContains(
            'news widget', 
            $result->getView()->render()
        );
        
        $_GET['path'] = '/document';
        $app = $this->getApp();
        $result = $app->compose($app->parse($app->getPath()));
        $this->assertContains(
            '<title>Document page</title>', 
            $result->getView()->render()
        );
        $this->assertContains(
            'currency widget', 
            $result->getView()->render()
        );
        $this->assertFalse(
            false !== strpos(
                $result->getView()->render(), 
                'news widget'
            )
        );
    }
}
