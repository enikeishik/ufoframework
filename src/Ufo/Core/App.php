<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

use Ufo\Cache\Cache;
use Ufo\Cache\CacheStorageNotSupportedException;
use Ufo\Modules\Controller;
use Ufo\Modules\ControllerInterface;
use Ufo\Modules\Renderable;
use Ufo\Modules\RenderableInterface;
use Ufo\Modules\View;
use Ufo\Modules\ViewInterface;
use Ufo\Routing\Route;
use Ufo\Routing\RouteArrayStorage;
use Ufo\Routing\RouteDbStorage;
use Ufo\Routing\RouteStorageInterface;
use Ufo\Routing\RouteStorageNotSetException;
use Ufo\Routing\RouteStorageEmptyException;
use Ufo\Widgets\WidgetsArrayStorage;
use Ufo\Widgets\WidgetsDbStorage;

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
     * @var \Ufo\Core\ConfigInterface
     */
    protected $config = null;
    
    /**
     * @var \Ufo\Core\DebugInterface
     */
    protected $debug = null;
    
    /**
     * @var array
     */
    protected $debugStack = [];
    
    /**
     * @var \Ufo\Cache\Cache
     */
    protected $cache = null;
    
    /**
     * @var \Ufo\Core\Db
     */
    protected $db = null;
    
    /**
     * @var string
     */
    protected $path = '';
    
    /**
     * @param \Ufo\Core\ConfigInterface $config
     * @param \Ufo\Core\DebugInterface $debug = null
     */
    public function __construct(ConfigInterface $config, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
    }
    
    /**
     * Application main workflow.
     * @return void
     */
    public function execute(): void
    {
        $path = '';
        
        try {
            $path = $this->getPath();
            
            if ($this->config->cache) {
                try {
                    $this->setCache();
                } catch (CacheStorageNotSupportedException $e) {
                    $this->cache = null;
                }
            }
            
            if (null !== $this->cache 
            && !$this->cache->expired($path, $this->config->cacheTtlWholePage)) {
                $result = $this->getCacheResult($this->cache->get($path));
            } else {
                $result = $this->compose($this->parse($path));
            }
            
        } catch (BadPathException $e) {
            $result = $this->getError(500, 'Bad path');
            
        } catch (RouteStorageNotSetException $e) {
            $result = $this->getError(500, 'Route storage not set');
            
        } catch (RouteStorageEmptyException $e) {
            $result = $this->getError(500, 'Route storage empty');
            
        } catch (DbConnectException $e) {
            if (null !== $this->cache && $this->cache->has($path)) {
                $result = $this->getCacheResult($this->cache->get($path));
            } else {
                $result = $this->getError(500, 'DataBase connection error');
            }
            
        } catch (SectionNotExistsException $e) {
            $result = $this->getError(404, 'Section not exists');
            
        } catch (SectionDisabledException $e) {
            $result = $this->getError(403, 'Section disabled');
            
        } catch (ModuleDisabledException $e) {
            $result = $this->getError(403, 'Section module disabled');
            
        } catch (ModuleParameterConflictException $e) {
            $result = $this->getError(404, 'Module parameter conflict with another');
            
        } catch (ModuleParameterFormatException $e) {
            $result = $this->getError(404, 'Module parameter bad format');
            
        } catch (ModuleParameterUnknownException $e) {
            $result = $this->getError(404, 'Module parameter unknown');
            
        } catch (\Exception $e) {
            $result = $this->getError(500, 'Unexpected exception: ' . $e->getMessage());
            
        }
        
        $this->sendHeaders($result->getHeaders());
        
        $this->render($result->getView());
    }
    
    /**
     * @return string
     * @throws \Ufo\Core\BadPathException
     */
    public function getPath(): string
    {
        if (!empty($this->path)) {
            return $this->path;
        }
        
        $this->debugTrace(__METHOD__);
        
        if (empty($_GET['path']) || '/' == $_GET['path']) {
            $this->path = '/';
        } elseif (Tools::isPath($_GET['path'])) {
            $this->path = $_GET['path'];
        } else {
            throw new BadPathException();
        }
        
        $this->debugTrace();
        
        return $this->path;
    }
    
    /**
     * @param string $path
     * @return \Ufo\Core\Section
     * @throws \Ufo\Core\DbConnectException
     * @throws \Ufo\Core\SectionNotExistsException
     * @throws \Ufo\Core\RouteStorageNotSetException
     * @throws \Ufo\Routing\RouteStorageEmptyException
     */
    public function parse(string $path): Section
    {
        $this->debugTrace(__METHOD__);
        
        if ($this->config->routeStorageType == $this->config::STORAGE_TYPE_DB) {
            $this->setDb();
        }
        
        $section = Route::parse($path, $this->getRouteStorage());
        if (null === $section) {
            throw new SectionNotExistsException();
        }
        
        $this->debugTrace();
        
        return $section;
    }
    
    /**
     * @return \Ufo\Core\Result
     * @throws \Ufo\Core\SectionDisabledException
     * @throws \Ufo\Core\ModuleDisabledException
     * @throws \Ufo\Core\ModuleParameterConflictException
     * @throws \Ufo\Core\ModuleParameterFormatException;
     * @throws \Ufo\Core\ModuleParameterUnknownException
     * @throws \Ufo\Core\DbConnectException
     */
    public function compose(Section $section): Result
    {
        if ($section->disabled) {
            throw new SectionDisabledException();
        }
        if ($section->module->disabled) {
            throw new ModuleDisabledException();
        }
        
        if (!$section->module->dbless) {
            $this->setDb();
        }
        
        //some middleware can change request params here
        
        $this->debugTrace(__METHOD__);
        
        $callback = $section->module->callback;
        if (is_callable($callback)) {
            $result = $this->composeCallback($callback, $section);
            
        } else {
            $controller = $this->getModuleController($section->module);
            if ($controller instanceof DIObjectInterface) {
                $controller->inject($this->getContainer());
            }
            
            $result = $controller->compose($section);
        }
        
        $this->debugTrace();
        
        if ($result->getView() instanceof ViewInterface) {
            return $this->composeWidgets($section, $result);
        }
        
        return $result;
    }
    
    /**
     * @param callable $callback
     * @param \Ufo\Core\Section $section
     * @return \Ufo\Core\Result
     */
    public function composeCallback(callable $callback, Section $section): Result
    {
            $callbackResult = $callback($this->getContainer(['section' => $section]));
            if ($callbackResult instanceof Result) {
                return $callbackResult;
            }
            $callbackResult = is_array($callbackResult) ? $callbackResult : ['content' => $callbackResult];
            
            $controller = $this->getDefaultController();
            
            $container = $this->getContainer();
            $container->set('data', array_merge($callbackResult, ['section' => $section]));
            
            $controller->inject($container);
            
            return $controller->compose($section);
    }
    
    /**
     * @param \Ufo\Core\Section $section
     * @param \Ufo\Core\Result $result
     * @return \Ufo\Core\Result
     */
    public function composeWidgets(Section $section, Result $result): Result
    {
        $this->debugTrace(__METHOD__);
        
        $view = $result->getView();
        
        $controller = $this->getDefaultController();
        $controller->inject($this->getContainer());
        
        $view->setWidgets($controller->composeWidgets($this->getWidgets($section)));
        
        $result->setView($view);
        
        $this->debugTrace();
        
        return $result;
    }
    
    /**
     * @param array $headers
     * @return void
     */
    public function sendHeaders(array $headers): void
    {
        foreach ($headers as $header) {
            header($header);
        }
    }
    
    /**
     * @param \Ufo\Modules\RenderableInterface $view
     * @return void
     */
    public function render(RenderableInterface $view): void
    {
        $this->debugTrace(__METHOD__);
        
        //some middleware can change response here
        
        if (null !== $this->cache && $view instanceof ViewInterface) {
            $content = $view->render();
            echo $content;
            $this->cache->set(
                $this->getPath(), 
                $content, 
                $this->config->cacheTtlWholePage
            );
        } else {
            echo $view->render();
        }
        
        $this->debugTrace();
    }
    
    /**
     * Returns widgets data grouped by place (place is a key).
     * @param \Ufo\Core\Section $section
     * @return array
     */
    public function getWidgets(Section $section): array
    {
        switch ($this->config->widgetsStorageType) {
            
            case $this->config::STORAGE_TYPE_DB:
                $storage = new WidgetsDbStorage($this->db);
                break;
                
            case $this->config::STORAGE_TYPE_ARRAY:
                if (!empty($this->config->widgetsStoragePath) 
                && file_exists($this->config->projectPath . $this->config->widgetsStoragePath)) {
                    $storageData = require_once $this->config->projectPath . $this->config->widgetsStoragePath;
                } else {
                    $storageData = $this->config->widgetsStorageData;
                }
                $storage = new WidgetsArrayStorage($storageData);
                break;
                
            default:
                return [];
                
        }
        
        return $storage->getWidgets($section);
    }
    
    /**
     * @param int $errCode = 200
     * @param string $errMessage = 'OK'
     * @param array $options = []
     * @return \Ufo\Core\Result
     */
    public function getError(int $errCode = 200, string $errMessage = 'OK', array $options = []): Result
    {
        if (null !== $this->db) {
            $this->db->close();
        }
        
        if (null !== $this->debug && count($this->debugStack) > 0) {
            for ($i = count($this->debugStack) - 1; $i >= 0; $i--) {
                $this->debugTrace();
            }
        }
        
        $headers = [];
        
        //TODO: HTTP version -> config, errMessage -> HTTP message (Http::CODE | $this->http[code])
        $headers[] = 'HTTP/1.0 ' . $errCode . ' ' . $errMessage;
        
        if ((301 == $errCode || 302 == $errCode) && !empty($options['location'])) {
            if (0 === strpos($options['location'], 'http')) {
                $headers[] = 'Location: ' . $options['location'];
            } else {
                $s = empty($_SERVER['HTTPS']) || 'off' == $_SERVER['HTTPS'] ? '' : 's';
                $host = empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST'];
                $headers[] = 'Location: ' . 'http' . $s . '://' . $host . $options['location'];
            }
        }
        
        $content = 'ERROR: (' . $errCode . ') ' . $errMessage . PHP_EOL;
        
        return new Result(new Renderable($content), $headers);
    }
    
    /**
     * @return \Ufo\Core\RouteStorageInterface
     * @throws \Ufo\Core\RouteStorageNotSetException
     * @throws \Ufo\Routing\RouteStorageEmptyException
     */
    protected function getRouteStorage(): RouteStorageInterface
    {
        switch ($this->config->routeStorageType) {
            case $this->config::STORAGE_TYPE_DB:
                return new RouteDbStorage($this->db);
            case $this->config::STORAGE_TYPE_ARRAY:
                if (!empty($this->config->routeStoragePath) 
                && file_exists($this->config->projectPath . $this->config->routeStoragePath)) {
                    $storageData = require_once $this->config->projectPath . $this->config->routeStoragePath;
                } else {
                    $storageData = $this->config->routeStorageData;
                }
                return new RouteArrayStorage($storageData);
        }
        
        throw new RouteStorageNotSetException();
    }
    
    /**
     * @return void
     * @throws \Ufo\Core\DbConnectException
     */
    protected function setDb(): void
    {
        if (null !== $this->db) {
            return;
        }
        $this->db = Db::getInstance($this->config, $this->debug);
    }
    
    /**
     * @return void
     * @throws \Ufo\Cache\CacheStorageNotSupportedException
     */
    protected function setCache(): void
    {
        if (null !== $this->cache) {
            return;
        }
        $this->cache = new Cache($this->config, $this->debug);
    }
    
    /**
     * @param string $value
     * @return \Ufo\Core\Result
     */
    protected function getCacheResult(string $value): Result
    {
        return new Result(new Renderable($value));
    }
    
    /**
     * @param array $options = []
     * @return \Ufo\Core\ContainerInterface
     */
    protected function getContainer(array $options = []): ContainerInterface
    {
        $di = [
            'debug'     => $this->debug, 
            'config'    => $this->config, 
            'db'        => $this->db, 
            'cache'     => $this->cache, 
            'app'       => $this, 
        ];
        return new Container(array_merge($di, $options));
    }
    
    /**
     * @param \Ufo\Core\Moule $module
     * @return \Ufo\Modules\ControllerInterface
     */
    protected function getModuleController(Module $module): ControllerInterface
    {
        $controllerClass = $module->callback;
        if (empty($controllerClass) || false === strpos($controllerClass, '\\')) {
            $controllerClass = 
                '\Ufo\Modules' . 
                '\\' . ucfirst($module->vendor) . 
                '\\' . ucfirst($module->name) . 
                '\Controller';
        }
        
        if (class_exists($controllerClass)) {
            return new $controllerClass();
        }
        
        return $this->getDefaultController();
    }
    
    /**
     * @return \Ufo\Modules\Controller
     */
    protected function getDefaultController(): Controller
    {
        return new Controller();
    }
    
    /**
     * @param string $operation = null
     * @return void
     */
    protected function debugTrace(string $operation = null): void
    {
        if (null === $this->debug) {
            return;
        }
        
        if (null !== $operation) {
            $this->debugStack[] = $this->debug->trace($operation);
        } else {
            $this->debug->traceClose(array_pop($this->debugStack));
        }
    }
}
