<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Routing\Route;
use \Ufo\Routing\RouteArrayStorage;
 
class RouteTest extends \Codeception\Test\Unit
{
    // tests
    public function testRouteParse()
    {
        $routeStorageData = require 'RouteStorageData.php';
        $section = Route::parse('/qwe/asd', new RouteArrayStorage($routeStorageData));
        $this->assertNotNull($section);
        $this->assertEquals('/qwe/asd', $section->path);
        $this->assertEquals('ASD qwe', $section->module->name);
    }
}
