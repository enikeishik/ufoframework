<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Routing\Route;
use \Ufo\Routing\RouteArrayStorage;
 
class RouteTest extends \Codeception\Test\Unit
{
    // tests
    public function testRouteParse()
    {
        $routeStorageData = require dirname(__DIR__) . '/_data/routes.php';
        $section = Route::parse('/section-with/callback', new RouteArrayStorage($routeStorageData));
        $this->assertNotNull($section);
        $this->assertEquals('/section-with/callback', $section->path);
        $this->assertEquals('Simple callback', $section->module->name);
    }
}
