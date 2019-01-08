<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Modules;

use Ufo\Core\Config;
use Ufo\Core\ContainerInterface;
use Ufo\Core\DebugInterface;
use Ufo\Core\DIObject;
use Ufo\Core\ModuleParameterUnknownException;
use Ufo\Core\Result;
use Ufo\Core\Section;

/**
 * Module level controller base class.
 */
class Controller extends DIObject implements ControllerInterface
{
    /**
     * @var \Ufo\Core\App
     */
    protected $app;
    
    /**
     * @var \Ufo\Core\Config
     */
    protected $config;
    
    /**
     * @var \Ufo\Core\DebugInterface
     */
    protected $debug;
    
    /**
     * External data passed through DI.
     * @var array
     */
    protected $data = [];
    
    /**
     * Module parameters coming from URL|GET|POST|COOKIE.
     * @var array
     */
    protected $params = [];
    
    /**
     * Buffer to keep already assigned parameters.
     * @var array
     */
    protected $paramsAssigned = [];
    
    /**
     * Initialization of structures of module parameters with default values.
     * @return void
     */
    protected function initParams(): void
    {
        $params = [
            Parameter::make('isRoot', 'bool', '', 'none', false, true), 
            Parameter::make('isRss', 'bool', 'rss', 'path', false, false), 
            Parameter::make('itemId', 'int', 'id', 'path', false, 0), 
            Parameter::make('page', 'int', 'page', 'path', true, 1), 
        ];
        
        foreach ($params as $param) {
            $this->params[$param->name] = $param;
        }
    }
    
    /**
     * Main controller method, compose all content.
     * @param \Ufo\Core\Section $section = null
     * @return \Ufo\Core\Result
     * @throws \Ufo\Core\ModuleParameterUnknownException
     */
    public function compose(Section $section = null): Result
    {
        $this->container->set('section', $section);
        
        if (null !== $section) {
            $this->initParams();
            $this->setParams($section->params);
        }
        
        $this->setData($section);
        
        $view = $this->getView();
        
        return new Result($view);
    }
    
    /**
     * @param \Ufo\Core\Section $section = null
     * @return void
     */
    protected function setData(Section $section = null): void
    {
        if (0 != count($this->data)) {
            return;
        }
        
        $model = $this->getModel();
        $this->container->set('model', $model);
        
        $this->data['section'] = $section;
        
        foreach (get_class_methods($model) as $method) {
            if (0 !== strpos($method, 'get')) {
                continue;
            }
            
            $this->data[strtolower(substr($method, 3))] = $model->$method();
        }
    }
    
    /**
     * @return \Ufo\Modules\ModelInterface
     */
    protected function getModel(): ModelInterface
    {
        $model = new Model();
        $model->inject($this->container);
        return $model;
    }
    
    /**
     * @return \Ufo\Modules\ViewInterface
     */
    protected function getView(): ViewInterface
    {
        $view = new View($this->config->templateDefault, $this->data);
        $view->inject($this->container);
        return $view;
    }
    
    /**
     * Create object for each widgets item.
     * @param array $widgets
     * @return array
     */
    public function composeWidgets(array $allWidgets): array
    {
        $container = clone $this->container;
        unset($container->widgets);
        unset($container->model);
        
        $widgetsResults = [];
        
        foreach ($allWidgets as $place => $placeWidgets) {
            $results = [];
            
            foreach ($placeWidgets as $widget) {
                $container->set('data', $widget);
                
                if (empty($widget['module'])) {
                    $widgetControllerClass = 
                        '\Ufo\Modules\Widgets\\' . 
                        ucfirst($widget['name']) . '\Controller';
                } else {
                    $widgetControllerClass = 
                        '\Ufo\Modules\\' . ucfirst($widget['module']) . '\\' . 
                        'Widget' . ucfirst($widget['name']) . 'Controller';
                }
                
                if (class_exists($widgetControllerClass)) {
                    $widgetController = new $widgetControllerClass();
                    $widgetController->inject($container);
                    $results[] = $widgetController->compose();
                    
                } else {
                    $defaultControllerClass = '\Ufo\Modules\Controller'; //defaultController
                    
                    $defaultController = new $defaultControllerClass();
                    $defaultController->inject($container);
                    $result = $defaultController->compose();
                    
                    $result->getView()->setTemplate($this->config->templateWidget); //change default template
                    $results[] = $result;
                }
            }
            
            $view = new View($this->config->templateWidgets, ['widgets' => $results]);
            $view->inject($this->container);
            $widgetsResults[$place] = $view;
        }
        
        return $widgetsResults;
    }
    
    /**
     * @param array $pathParams
     * @return void
     * @throws \Ufo\Core\ModuleParameterUnknownException
     */
    protected function setParams(array $pathParams): void
    {
        $this->setParamsFromPath($pathParams);
        
        $this->setParamsFromQs();
        
        $this->setIsRootParam(); //set before setBoolParams!
        
        $this->setBoolParams();
    }
    
    /**
     * @param array $pathParams
     * @return void
     * @throws \Ufo\Core\ModuleParameterUnknownException
     */
    protected function setParamsFromPath(array $pathParams): void
    {
        foreach ($pathParams as $pathParam) {
            if (!$this->setParam($pathParam)) {
                throw new ModuleParameterUnknownException();
            }
        }
    }
    
    /**
     * @return void
     */
    protected function setParamsFromQs(): void
    {
        foreach ($this->params as $paramName => $paramSet) {
            if ('get' != $paramSet->from || !isset($_GET[$paramSet->prefix])) {
                continue;
            }
            switch ($paramSet->type) {
                case 'int':
                    $this->params[$paramName]->value = (int) $_GET[$paramSet->prefix];
                    break;
                case 'bool':
                    $this->params[$paramName]->value = true;
                    break;
                default:
                    $this->params[$paramName]->value = $_GET[$paramSet->prefix];
            }
        }
    }
    
    /**
     * @return void
     */
    protected function setIsRootParam(): void
    {
        foreach ($this->params as $paramName => $paramSet) {
            if ('isRoot' != $paramName && null !== $paramSet->value) {
                $this->params['isRoot']->value = false;
                break;
            }
        }
    }
    
    /**
     * @return void
     */
    protected function setBoolParams(): void
    {
        foreach ($this->params as $paramName => $paramSet) {
            if ('bool' == $paramSet->type && null === $paramSet->value) {
                $this->params[$paramName]->value = $paramSet->defval;
            }
        }
    }
    
    /**
     * Search $pathParam in module parameters and set module parameter value if found.
     * @param string $pathParam
     * @return bool
     */
    protected function setParam(string $pathParam): bool
    {
        foreach ($this->params as $paramName => $paramSet) {
            if ('path' != $paramSet->from) {
                continue;
            }
            if (in_array($paramName, $this->paramsAssigned)) {
                return false;
            }
            
            if ('' != $paramSet->prefix 
            && 0 === strpos($pathParam, $paramSet->prefix)) { //for named params
                //in case of more than one parameters coming
                //(например идентификатор элемента и дата) выборки, выдаем ошибку 404, 
                //поскольку иначе будет неоднозначность и дублирование страниц
                // /section/id123/dt2017 | /section/dt2017/id123
                if (!$paramSet->additional 
                && in_array('all', $this->paramsAssigned)) {
                    return false;
                }
                $val = substr($pathParam, strlen($paramSet->prefix));
                switch ($paramSet->type) {
                    case 'int':
                        $this->params[$paramName]->value = (int) $val;
                        break;
                    case 'bool':
                        $this->params[$paramName]->value = true;
                        break;
                    default:
                        $this->params[$paramName]->value = $val;
                }
                if ($paramSet->additional) {
                    $this->paramsAssigned[] = $paramName;
                } else {
                    $this->paramsAssigned[] = 'all';
                }
                return true;
                
            } elseif ('itemId' == $paramName && ctype_digit($pathParam)) { //for itemId
                if (in_array('all', $this->paramsAssigned)) {
                    return false;
                }
                $this->params[$paramName]->value = (int) $pathParam;
                $this->paramsAssigned[] = 'all';
                return true;
                
            } elseif ('date' == $paramName && 10 == strlen($pathParam) && false !== strtotime($pathParam)) { //for date
                if (in_array('all', $this->paramsAssigned)) {
                    return false;
                }
                $date = strtotime($pathParam);
                //BOOKMARK: DateTime format
                $this->params[$paramName]->value = date('Y-m-d', $date);
                $this->paramsAssigned[] = 'all';
                return true;
                
            } elseif ('' == $paramSet->prefix && null === $paramSet->value) { //for param without prefix
                $this->params[$paramName]->value = $pathParam;
                return true;
            }
        }
        
        return false;
    }
}
