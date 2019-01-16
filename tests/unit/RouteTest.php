<?php
use \Ufo\Routing\Route;
use \Ufo\Routing\RouteArrayStorage;
 
class RouteTest extends BaseUnitTest
{
    // tests
    public function testRouteParse()
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
}
