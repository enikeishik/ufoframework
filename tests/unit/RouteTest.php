<?php
use \Ufo\Core\Config;
use \Ufo\Core\Db;
use \Ufo\Routing\Route;
use \Ufo\Routing\RouteArrayStorage;
use \Ufo\Routing\RouteDbStorage;
 
class RouteTest extends BaseUnitTest
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    // tests
    public function testArrayStorage()
    {
        $this->expectedException(
            \Ufo\Routing\RouteStorageEmptyException::class, 
            function() { new RouteArrayStorage([]); }
        );
        
        $routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $section = Route::parse('/section-with/callback', new RouteArrayStorage($routeStorageData));
        $this->assertNotNull($section);
        $this->assertEquals('/section-with/callback', $section->path);
        $this->assertEquals('Simple callback', $section->module->name);
    }
    
    public function testDbStorage()
    {
        $config = new Config();
        $config->loadFromIni(dirname(__DIR__) . '/_data/.config', true);
        $db = Db::getInstance($config);
        
        $this->expectedException(
            \Ufo\Routing\RouteStorageEmptyException::class, 
            function() use($db) { new RouteDbStorage($db); }
        );
        
        $this->tester->haveInDatabase(
            'sections', 
            [
                'id'        => 1001, 
                'path'      => '/document', 
                'title'     => 'Document page', 
                'module'    => 'UfoDocuments', 
            ]
        );
        $this->tester->haveInDatabase(
            'modules', 
            [
                'id'            => 1001, 
                'vendor'        => 'Ufo', 
                'name'          => 'Documents', 
                'package'       => 'UfoDocuments', 
                'title'         => 'Documents', 
                'description'   => 'Simple Documents module', 
            ]
        );
        
        $section = Route::parse('/document', new RouteDbStorage($db));
        $this->assertNotNull($section);
        $this->assertEquals('/document', $section->path);
        $this->assertEquals('Documents', $section->module->name);
        
        $section = Route::parse('', new RouteDbStorage($db));
        $this->assertNull($section);
        
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
        
        $section = Route::parse('', new RouteDbStorage($db));
        $this->assertNotNull($section);
        $this->assertEquals('/', $section->path);
        $this->assertEquals('Mainpage', $section->module->name);
        
        $section = Route::parse('/non-existence-section', new RouteDbStorage($db));
        $this->assertNull($section);
    }
}
