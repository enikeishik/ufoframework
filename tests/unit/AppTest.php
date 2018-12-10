<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
use \Ufo\Core\Debug;
use \Ufo\Core\App;
 
class AppTest extends \Codeception\Test\Unit
{
    // tests
    public function testApp()
    {
        $config = new Config();
        $app = new App($config, new Debug());
        $config->routeStorageData = require 'RouteStorageData.php';
        
        $_GET['path'] = '/qwe/asd';
        $app->execute();
    }
}
