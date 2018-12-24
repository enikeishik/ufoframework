<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Routing\Route;
use \Ufo\Routing\RouteArrayStorage;
 
class RouteTest extends \Codeception\Test\Unit
{
    // tests
    public function testRouteParse()
    {
        $routeStorageData = require dirname(__DIR__) . '/_data/RouteStorageData.php';
        $section = Route::parse('/qwe/asd', new RouteArrayStorage($routeStorageData));
        $this->assertNotNull($section);
        $this->assertEquals('/qwe/asd', $section->path);
        $this->assertEquals('Simple callback', $section->module->name);
    }
}
