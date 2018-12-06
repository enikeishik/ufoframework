<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Routing\Route;
use \Ufo\Routing\RouteArrayStorage;
 
class RouteTest extends \Codeception\Test\Unit
{
    protected $storage = [
        '/' => [
            'module' => [
                'id' => -1, 
                'name' => 'Main page', 
                'controller' => 'Ufo\Modules\Mainpage\Controller', 
                'model' => 'Ufo\Modules\Mainpage\Model', 
                'view' => 'Ufo\Modules\Mainpage\View', 
            ], 
        ], 
        '/asd' => ['module' => ['id' => 1]], 
        '/asd/qwe' => ['module' => ['id' => 2]], 
        '/qwe' => ['module' => ['id' => 3]], 
        '/qwe/asd' => [
            'module' => [
                'id' => 333, 
                'name' => 'ASD qwe', 
            ], 
        ], 
        '/qwe/asd/zxc' => ['module' => ['id' => 4]], 
    ];
    
    // tests
    public function testRouteParse()
    {
        var_dump(Route::parse('/qwe/asd', new RouteArrayStorage($this->storage))); exit();
    }
}
