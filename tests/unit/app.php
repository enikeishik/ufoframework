<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
use \Ufo\Core\Debug;
use \Ufo\Core\App;

$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../..';
//var_dump($_SERVER['DOCUMENT_ROOT']);
$config = new Config();
$debug = new Debug();
$app = new App($config, $debug);
$config->routeStorageData = require 'RouteStorageData.php';
$config->widgetsStorageData = require 'WidgetsStorageData.php';

//$_GET['path'] = '/qwe/asd';
$debug->trace('execute');
$app->execute();
// $result = $app->compose($app->parse());
// $result->getHeaders();
// $result->getContent();
$debug->traceEnd();
echo 
    PHP_EOL . 
    'execution time: ' . round(100 * $debug->getExecutionTime(), 2) . ' ms; ' . 
    'mem: ' . number_format(memory_get_peak_usage()) . 
    PHP_EOL;
//var_dump($debug->getTrace());
