<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
use \Ufo\Core\Debug;
use \Ufo\Core\App;
 
class AppTest extends \Codeception\Test\Unit
{
    /**
     * @param string $expectedExceptionClass
     * @param callable $call = null
     */
    protected function expectedException(string $expectedExceptionClass, callable $call = null)
    {
        try {
            $call();
        } catch (\Exception $e) {
            $this->assertEquals($expectedExceptionClass, get_class($e));
        }
    }
    
    // tests
    public function testApp()
    {
        $config = new Config();
        $app = new App($config, new Debug());
        $config->routeStorageData = require 'RouteStorageData.php';
        
        $_GET['path'] = '/qwe/asd';
        $result = $app->compose($app->parse());
        $this->assertNotNull($result);
        $this->assertEquals([], $result->getHeaders());
        $this->assertNotEquals('', $result->getContent());
        
        $_GET['path'] = '/!qwe/asd';
        $this->expectedException(
            \Ufo\Core\BadPathException::class, 
            function() use($app) { $app->execute(); }
        );
        
        $_GET['path'] = '/not2exists2path2qwe/asd';
        $this->expectedException(
            \Ufo\Core\SectionNotExistsException::class, 
            function() use($app) { $app->execute(); }
        );
        
        $_GET['path'] = '/asd';
        $this->expectedException(
            \Ufo\Core\SectionDisabledException::class, 
            function() use($app) { $app->execute(); }
        );
        
        $_GET['path'] = '/asd/qwe';
        $this->expectedException(
            \Ufo\Core\ModuleDisabledException::class, 
            function() use($app) { $app->execute(); }
        );
    }
}
