<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Config;
use \Ufo\Debug;
use \Ufo\App;
 
class AppTest extends \Codeception\Test\Unit
{
    // tests
    public function testApp()
    {
        $app = new App(new Config(), new Debug());
    }
}
