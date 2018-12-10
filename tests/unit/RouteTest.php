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
        var_dump(Route::parse('/qwe/asd', new RouteArrayStorage($routeStorageData))); exit();
    }
}
