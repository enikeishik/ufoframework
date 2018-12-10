<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

use Ufo\Routing\Route;
use Ufo\Routing\RouteArrayStorage;
use Ufo\Routing\RouteDbStorage;
use Ufo\Routing\RouteStorageInterface;
use Ufo\Modules\Controller;

/**
 * Main application class.
 * 
 * app collect and parse request data
 *      middleware here
 * select section/module and execute its controller
 * controller generates content and/or headers and return it as response
 *      middleware here
 * app render content and send headers
 * 
 */
class App
{
    /**
     * @var ConfigInterface
     */
    protected $config = null;
    
    /**
     * @var DebugInterface
     */
    protected $debug = null;
    
    /**
     * @var Db
     */
    protected $db = null;
    
    /**
     * @param ConfigInterface $config
     * @param DebugInterface $debug = null
     */
    public function __construct(ConfigInterface $config, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
    }
    
    /**
     * @return void
     */
    public function execute(): void
    {
        $result = $this->compose($this->parse());
        //$this->sendHeaders($result->getHeaders());
        $this->render($result->getContent());
    }
    
    public function parse(): Section
    {
        $path = $this->getPath();
        if (null === $path) {
            return $this->getError(1, 'Bad path');
        }
        
        //cache
        if (false) {
            //return chache->getResult ?
        }
        
        if ($this->config->routeStorageType == $this->config::STORAGE_TYPE_DB) {
            $this->setDb();
        }
        
        return Route::parse($path, $this->getRouteStorage());
    }
    
    public function compose(Section $section): Result
    {
        if (null === $section) {
            return $this->getError(404, 'Sections not exists');
        }
        if ($section->disabled) {
            return $this->getError(403, 'Section disabled');
        }
        if ($section->module->disabled) {
            return $this->getError(403, 'Section module disabled');
        }
        
        if (!$section->module->dbless) {
            $this->setDb();
        }
        
        //some middleware can change request params here
        
        $callback = $section->module->callback;
        if (is_callable($callback)) {
            return $callback($this->getContainer($section));
        }
        
        $controller = $this->getModuleController($section->module);
        $controller->inject($this->getContainer($section));
        
        return $controller->execute();
    }
    
    public function render(string $content): void
    {
        //some middleware can change response here
        ob_end_clean(); echo PHP_EOL; //to display output in codeception tests
        
        echo $content;
        
        echo PHP_EOL; //to display output in codeception tests
        
        //cache
    }
    
    /**
     * @param int $errCode = 200
     * @param string $errMessage = 'OK'
     * @param array $options = []
     * @return Result
     */
    public function getError(int $errCode = 200, string $errMessage = 'OK', array $options = []): Result
    {
        if (null !== $this->db) {
            $this->db->close();
        }
        
        $headers = [];
        
        if ((301 == $errCode || 302 == $errCode) && !empty($options['location'])) {
            $headers[] = 'Location: ' . $options['location'];
        }
        
        $content = 'ERROR: (' . $errCode . ') ' . $errMessage . PHP_EOL;
        
        return new Result($content, $headers);
    }
    
    /**
     * @return string|null
     */
    protected function getPath(): ?string
    {
        if (empty($_GET['path']) || '/' == $_GET['path']) {
            return '/';
        } else {
            return Tools::isPath($_GET['path']) ? $_GET['path'] : null;
        }
    }
    
    /**
     * @return RouteStorageInterface
     * @throws RouteStorageNotSetException
     */
    protected function getRouteStorage(): RouteStorageInterface
    {
        switch ($this->config->routeStorageType) {
            case $this->config::STORAGE_TYPE_DB:
                return new RouteDbStorage($this->db);
            case $this->config::STORAGE_TYPE_ARRAY:
                if (!empty($this->config->routeStoragePath) 
                && file_exists($this->config->rootPath . $this->config->routeStoragePath)) {
                    $routeStorageData = require_once $this->config->rootPath . $this->config->routeStoragePath;
                } else {
                    $routeStorageData = $this->config->routeStorageData;
                }
                return new RouteArrayStorage($routeStorageData);
        }
        
        throw new RouteStorageNotSetException();
    }
    
    /**
     * @return void
     */
    protected function setDb(): void
    {
        if (null !== $this->db) {
            return;
        }
        $this->db = Db::get($this->debug);
    }
    
    /**
     * @param Section $section
     * @return ContainerInterface
     */
    protected function getContainer(Section $section): ContainerInterface
    {
        $di = [
            'debug'     => $this->debug, 
            'config'    => $this->config, 
            'section'   => $section, 
        ];
        if (!$section->module->dbless) {
            $di['db'] = $this->db;
        }
        return new Container($di);
    }
    
    /**
     * @param Moule $module
     * @return Controller
     * @throws RouteStorageNotSetException
     */
    protected function getModuleController(Module $module): Controller
    {
        $controllerClass = $module->callback;
        if (empty($controllerClass) || false === strpos($controllerClass, '\\')) {
            $controllerClass = '\Ufo\Modules\\' . $module->name . '\Controller';
        }
        if (!class_exists($controllerClass)) {
            $controllerClass = '\Ufo\Modules\Controller'; //defaultController
        }
        
        if (class_exists($controllerClass)) {
            return new $controllerClass();
        }
        
        throw new ControllerNotSetException();
    }
}
