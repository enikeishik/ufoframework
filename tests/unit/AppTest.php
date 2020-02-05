<?php
use \Ufo\Core\Config;
use \Ufo\Core\Debug;
use \Ufo\Core\App;
use \Ufo\Core\Result;
use \Ufo\Core\Section;
use \Ufo\Core\Widget;
use \Ufo\Modules\Controller;
use \Ufo\Modules\Parameter;
use \Ufo\Modules\Renderable;
use \Ufo\Modules\View;
 
class AppTest extends BaseUnitTest
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function getApp($withDebug = true, $withCache = false)
    {
        $config = new Config();
        $config->routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $config->widgetsStorageData = require dirname(__DIR__) . '/_data/widgets.php';
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        if ($withCache) {
            $config->cache = true;
            $config->cacheType = Config::CACHE_TYPE_ARRAY;
        }
        return new App($config, $withDebug ? new Debug() : null);
    }
    
    protected function getAppDb($withDebug = true, $withCache = false)
    {
        $config = new Config();
        $config->loadFromIni(dirname(__DIR__) . '/_data/.config', true);
        $config->routeStorageType = Config::STORAGE_TYPE_DB;
        $config->routeStorageTable = '#__sections';
        $config->routeStorageKeyField = 'path';
        $config->routeStorageDataField = '*';
        $config->widgetsStorageType = Config::STORAGE_TYPE_DB;
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        if ($withCache) {
            $config->cache = true;
            $config->cacheType = Config::CACHE_TYPE_ARRAY;
        }
        return new App($config, $withDebug ? new Debug() : null);
    }
    
    protected function assertArrayContentContains($needle, $withDebug = true, $withCache = false)
    {
        $app = $this->getApp($withDebug, $withCache);
        ob_start();
        $app->execute();
        $content = ob_get_clean();
        $this->assertContains($needle, $content);
    }
    
    protected function assertDbContentContains($needle, $withDebug = true, $withCache = false)
    {
        $app = $this->getAppDb($withDebug, $withCache);
        ob_start();
        $app->execute();
        $content = ob_get_clean();
        $this->assertContains($needle, $content);
    }
    
    // tests
    public function testApp()
    {
        $app = $this->getApp();
        $this->assertInstanceOf(App::class, $app);
    }
    
    public function testExecute()
    {
        $config = new Config();
        $config->routeStorageType = null;
        $app = new App($config);
        ob_start();
        $app->execute();
        $content = ob_get_clean();
        $this->assertContains('Route storage not set', $content);
        
        $config = new Config();
        $config->routeStorageType = Config::STORAGE_TYPE_DB;
        $config->dbServer = 'unreacheble.host.local';
        $config->cache = false;
        $app = new App($config);
        ob_start();
        $app->execute();
        $content = ob_get_clean();
        $this->assertContains('DataBase connection error', $content);
        
        $_GET['path'] = '/some/another/document';
        $config = new Config();
        $config->cache = true;
        $config->cacheType = Config::CACHE_TYPE_SQLITE;
        $config->rootPath = '';
        $config->cacheSqliteBase = dirname(__DIR__) . '/_data/cache.db';
        $config->cacheTtlWholePage = 0;
        $config->routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $config->widgetsStorageData = require dirname(__DIR__) . '/_data/widgets.php';
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        $app = new class($config) extends App {
            protected function getCacheResult(string $value): Result
            {
                return parent::getCacheResult($value . PHP_EOL . 'cache result');
            }
            public function cacheClear()
            {
                if ($this->cache !== null) {
                    $this->cache->clear();
                }
            }
        };
        $app->execute();
        $app->cacheClear();
        $app->execute();
        $config = new Config();
        $config->routeStorageType = Config::STORAGE_TYPE_DB;
        $config->dbServer = 'unreacheble.host.local';
        $config->cache = true;
        $config->cacheType = Config::CACHE_TYPE_SQLITE;
        $config->rootPath = '';
        $config->cacheSqliteBase = dirname(__DIR__) . '/_data/cache.db';
        $config->cacheTtlWholePage = 0;
        $config->routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $config->widgetsStorageData = require dirname(__DIR__) . '/_data/widgets.php';
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        $app = new class($config) extends App {
            protected function getCacheResult(string $value): Result
            {
                return parent::getCacheResult($value . PHP_EOL . 'cache result');
            }
        };
        ob_start();
        $app->execute();
        $content = ob_get_clean();
        $this->assertContains('<title>QWE ASD ZXC page</title>', $content);
        $this->assertContains('cache result', $content);
        unset($_GET['path']);
        
        $config = new Config();
        unset($config->routeStorageType);
        $config->cache = false;
        $app = new App($config);
        ob_start();
        $app->execute();
        $content = ob_get_clean();
        $this->assertContains('Unexpected exception', $content);
    }
    
    public function testExecuteWithArray()
    {
        $this->assertArrayContentContains('<title>Main page</title>');
        $this->assertArrayContentContains('<title>Main page</title>', false);
        $this->assertArrayContentContains('<title>Main page</title>', false, true);
        $this->assertArrayContentContains('<title>Main page</title>', true, true);
        
        $_GET['path'] = '/!document';
        $this->assertArrayContentContains('Bad path');
        
        $_GET['path'] = '/not2exists2path2qwe/asd';
        $this->assertArrayContentContains('Section not exists');
        
        $_GET['path'] = '/section-disabled';
        $this->assertArrayContentContains('Section disabled');
        
        $_GET['path'] = '/module/disabled';
        $this->assertArrayContentContains('Section module disabled');
        
        $_GET['path'] = '/document/123/rss';
        $this->assertArrayContentContains('Module parameter conflict with another');
        
        $_GET['path'] = '/document/dt1970-01-aa';
        $config = new Config();
        $config->routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $config->widgetsStorageData = require dirname(__DIR__) . '/_data/widgets.php';
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        $app = new class($config) extends App {
            protected function getDefaultController(): Controller
            {
                return new class() extends Controller {
                    protected function initParams(): void
                    {
                        parent::initParams();
                        $this->params['testBadDate'] = 
                            Parameter::make('testBadDate', 'date', 'dt', 'path', true, 0);
                    }
                };
            }
        };
        ob_start();
        $app->execute();
        $content = ob_get_clean();
        $this->assertContains('Module parameter bad format', $content);
        
        $_GET['path'] = '/document/123/asd';
        $this->assertArrayContentContains('Module parameter unknown');
    }
    
    /**
     * @group mysql
     */
    public function testExecuteWithDb()
    {
        $this->assertDbContentContains('Section not exists');
        
        $this->tester->haveInDatabase(
            'sections', 
            [
                'id'        => 1000, 
                'path'      => '/', 
                'title'     => 'Main page', 
                'module'    => 'UfoMainpage', 
            ]
        );
        $this->tester->haveInDatabase(
            'modules', 
            [
                'id'            => 1000, 
                'vendor'        => 'Ufo', 
                'name'          => 'Mainpage', 
                'package'       => 'UfoMainpage', 
                'title'         => 'mainpage', 
                'description'   => 'Simple Mainpage module', 
            ]
        );
        $this->tester->haveInDatabase(
            'widgets', 
            [
                'id'            => 1000, 
                'section_id'    => 0, 
                'place'         => 'left col top', 
                'widget'        => json_encode(new Widget([
                    'vendor' => 'ufo', 
                    'module' => '', 
                    'name'   => 'gismeteo', 
                    'title'  => 'gismeteo db widget title', 
                    'text'   => 'gismeteo db widget content', 
                ])), 
            ]
        );
        $this->assertDbContentContains('<title>Main page</title>');
        $this->assertDbContentContains('gismeteo db widget title');
    }
    
    /**
     * @group mysql
     */
    public function testExecuteWithDbWithNullModule()
    {
        $this->tester->haveInDatabase(
            'sections', 
            [
                'id'        => 1000, 
                'path'      => '/', 
                'title'     => 'Main page', 
                'module'    => null, 
            ]
        );
        $this->assertDbContentContains('Module for current section not set');
    }
    
    /**
     * @group mysql
     */
    public function testExecuteWithDbWithModuleNotExists()
    {
        $this->tester->haveInDatabase(
            'sections', 
            [
                'id'        => 1000, 
                'path'      => '/', 
                'title'     => 'Main page', 
                'module'    => 'UfoMainpage', 
            ]
        );
        $this->assertDbContentContains('Module for current section not set');
    }
    
    public function testExecuteWithCache()
    {
        $config = new Config();
        $config->cache = true;
        $config->cacheType = 'unsupported type';
        $app = new class($config) extends App {
            public $cache = null;
        };
        $this->expectedException(
            '', 
            function() use ($app) { $app->execute(); }
        );
        $this->assertNull($app->cache);
        
        
        $config = new Config();
        $config->cache = true;
        $config->cacheType = Config::CACHE_TYPE_SQLITE;
        $config->rootPath = '';
        $config->cacheSqliteBase = dirname(__DIR__) . '/_data/cache.db';
        $config->routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $config->widgetsStorageData = require dirname(__DIR__) . '/_data/widgets.php';
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        $app = new class($config) extends App {
            public $cache = null;
            protected function getCacheResult(string $value): Result
            {
                return parent::getCacheResult($value . PHP_EOL . 'cache result');
            }
            public function cacheClear()
            {
                if ($this->cache !== null) {
                    $this->cache->clear();
                }
            }
        };
        $app->execute();
        $app->cacheClear();
        ob_start();
        $app->execute();
        $content1 = ob_get_clean();
        ob_start();
        $app->execute();
        $content2 = ob_get_clean();
        $this->assertEquals($content1 . PHP_EOL . 'cache result', $content2);
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
    
    public function testParse()
    {
        $config = new Config();
        $config->routeStorageType = null;
        $app = new App($config);
        $this->expectedException(
            \Ufo\Core\RouteStorageNotSetException::class, 
            function() use($app) { $app->parse('/'); }
        );
        
        $config = new Config();
        $config->routeStorageType = Config::STORAGE_TYPE_ARRAY;
        $config->projectPath = '';
        $config->routeStoragePath = dirname(__DIR__) . '/_data/routes.php';
        $app = new App($config);
        $section = $app->parse('/document');
        $this->assertInstanceOf(Section::class, $section);
        $this->assertEquals('/document', $section->path);
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
        
$tpl = <<<EOD
namespace Ufo\Modules\Somevendor\Somemodule;
class Controller extends \Ufo\Core\DIObject implements \Ufo\Modules\ControllerInterface
{
    public function compose(?\Ufo\Core\Section \$section = null): \Ufo\Core\Result
    {
        return new \Ufo\Core\Result(new \Ufo\Modules\Renderable('some vendor module content'));
    }
}
EOD;
        eval($tpl);
        $_GET['path'] = '/some-vendor-some-module';
        $app = $this->getApp();
        $result = $app->compose($app->parse($app->getPath()));
        $this->assertNotNull($result);
        $this->assertEquals([], $result->getHeaders());
        $this->assertContains('some vendor module content', $result->getView()->render());
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
        $this->assertNotContains(
            'bad widget', 
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
        
        $config = new Config();
        $config->widgetsStorageType = 'unsupported storage';
        $app = new App($config);
        $this->assertEquals([], $app->getWidgetsData(new Section()));
        
        $config = new Config();
        $config->projectPath = '';
        $config->widgetsStorageType = Config::STORAGE_TYPE_ARRAY;
        $config->widgetsStoragePath = dirname(__DIR__) . '/_data/widgets.php';
        $app = new App($config);
        $widgets = $app->getWidgetsData(new Section(['path' => '/document']));
        $this->assertTrue(is_array($widgets));
        $this->assertCount(3, $widgets);
        
        $config = new Config();
        $config->loadFromIni(dirname(__DIR__) . '/_data/.config', true);
        $config->projectPath = '';
        $config->widgetsStorageType = Config::STORAGE_TYPE_ARRAY;
        $config->widgetsStoragePath = '';
        $config->widgetsStorageData = [
            '/document' => [
                'left col top' => [
                    [
                        'vendor' => 'ufo', 
                        'module' => '', 
                        'name' => '', 
                        'title' => '', 
                        'text' => '', 
                        'dbless' => false, 
                    ]
                ], 
            ], 
        ];
        $app = new App($config);
        $result = $app->composeWidgets(
            new Section(['path' => '/document']), 
            new Result(new View())
        );
    }
}
