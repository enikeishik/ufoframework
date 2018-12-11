<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
use \Ufo\Core\Debug;
use \Ufo\Core\App;

$config = new Config();
$debug = new Debug();
$app = new App($config, $debug);
$config->routeStorageData = require 'RouteStorageData.php';

//$_GET['path'] = '/qwe/asd';
$debug->trace('execute');
$app->execute();
// $result = $app->compose($app->parse());
// $result->getHeaders();
// $result->getContent();
$debug->traceEnd();
echo PHP_EOL . round(100 * $debug->getExecutionTime(), 2) . PHP_EOL;
var_dump($debug->getTrace());
