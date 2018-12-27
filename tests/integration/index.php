<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
use \Ufo\Core\Debug;
use \Ufo\Core\App;

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__); //tests
}
if (!empty($argv[1])) {
    $_GET['path'] = $argv[1];
}

$config = new Config();
$config->routeStorageData = require dirname(__DIR__) . '/_data/RouteStorageData.php';
$config->widgetsStorageData = require dirname(__DIR__) . '/_data/WidgetsStorageData.php';
$config->templatesPath = '/tests/integration/templates';
$config->templatesDefault = '';
$debug = new Debug();
$app = new App($config, $debug);

$debug->trace('execute');
$app->execute();
$debug->traceEnd();

echo 
    PHP_EOL . 
    'execution time: ' . round(100 * $debug->getExecutionTime(), 2) . ' ms; ' . 
    'mem: ' . number_format(memory_get_peak_usage()) . 
    PHP_EOL;
var_dump($debug->getTrace());
