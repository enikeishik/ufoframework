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
use Ufo\Core\DIObjectInterface;
use Ufo\Core\ModuleParameterConflictException;
use Ufo\Core\ModuleParameterFormatException;
use Ufo\Core\ModuleParameterUnknownException;
use Ufo\Core\Result;
use Ufo\Core\Section;
use Ufo\Core\Tools;
use Ufo\Core\Widget;

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
     * Main controller method, compose all content.
     * @param \Ufo\Core\Section $section = null
     * @return \Ufo\Core\Result
     * @throws \Ufo\Core\ModuleParameterConflictException
     * @throws \Ufo\Core\ModuleParameterFormatException;
     * @throws \Ufo\Core\ModuleParameterUnknownException
     */
    public function compose(Section $section = null): Result
    {
        $this->container->set('section', $section);
        
        if (null !== $section) {
            $this->initParams();
            $this->setParams($section->params);
            $this->container->set('params', $this->params);
        }
        
        $this->setData($section);
        
        return new Result($this->getView());
    }
    
    /**
     * @param \Ufo\Modules\Parameter $param
     * @return void
     */
    protected function addParam(Parameter $param): void
    {
        $this->params[$param->name] = $param;
    }
    
    /**
     * @param array $params
     * @return void
     */
    protected function addParams(array $params): void
    {
        foreach ($params as $param) {
            $this->addParam($param);
        }
    }
    
    /**
     * Initialization of structures of module parameters with default values.
     * @return void
     */
    protected function initParams(): void
    {
        if (0 != count($this->params)) {
            return;
        }
        
        $this->addParams([
            Parameter::makeBool('isRoot', '', 'none', false, true), 
            Parameter::makeBool('isRss', 'rss', 'path', false, false), 
            Parameter::makeInt('itemId', '', 'path', false, 0), 
            Parameter::makeInt('page', 'page', 'path', true, 1), 
        ]);
    }
    
    /**
     * Moves all (path) params without prefix to the end.
     * This implementation faster than uasort.
     * @return void
     */
    protected function sortParams(): void
    {
        $sorted = [];
        foreach ($this->params as $paramName => $paramSet) {
            if (0 != strlen($paramSet->prefix)) {
                $sorted[$paramName] = $paramSet;
            }
        }
        foreach ($this->params as $paramName => $paramSet) {
            if (0 == strlen($paramSet->prefix)) {
                $sorted[$paramName] = $paramSet;
            }
        }
        $this->params = $sorted;
    }
    
    /**
     * @param array $pathParams
     * @return void
     * @throws \Ufo\Core\ModuleParameterConflictException
     * @throws \Ufo\Core\ModuleParameterFormatException;
     * @throws \Ufo\Core\ModuleParameterUnknownException
     */
    protected function setParams(array $pathParams): void
    {
        if (0 != count($this->paramsAssigned)) {
            return;
        }
        
        $this->sortParams();
        
        $this->setParamsFromPath($pathParams);
        
        $this->setParamsFromQs();
        
        $this->setIsRootParam(); //set before setBoolParams!
        
        $this->setBoolParams();
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
        
        $this->data['section'] = $section;
        
        $this->setDataFromModel($this->getModel());
    }
    
    /**
     * This implementation calls all methods with prefix `get` from model.
     * Implementation may be overridden in inherited method 
     * to call only necessary model methods dependently from parameters.
     * @param \Ufo\Modules\ModelInterface $model
     * @return void
     */
    protected function setDataFromModel(ModelInterface $model): void
    {
        $this->container->set('model', $model);
        
        foreach (get_class_methods($model) as $method) {
            if (0 !== strpos($method, 'get')) {
                continue;
            }
            
            $this->data[strtolower(substr($method, 3))] = $model->$method();
        }
    }
    
    /**
     * Must be overridden in child class to gets object from child NS.
     * @return \Ufo\Modules\ModelInterface
     */
    protected function getModelObject(): ModelInterface
    {
        return new Model();
    }
    
    /**
     * Must be overridden in child class to gets object from child NS.
     * @return \Ufo\Modules\ViewInterface
     */
    protected function getViewObject(): ViewInterface
    {
        return new View();
    }
    
    /**
     * @return \Ufo\Modules\ModelInterface
     */
    protected function getModel(): ModelInterface
    {
        $model = $this->getModelObject();
        if ($model instanceof DIObjectInterface) {
            $model->inject($this->container);
        }
        return $model;
    }
    
    /**
     * @return \Ufo\Modules\ViewInterface
     */
    protected function getView(): ViewInterface
    {
        $view = $this->getViewObject();
        if ('' == $view->getTemplate()) {
            $view->setTemplate($this->config->templateDefault);
        }
        if (0 == count($view->getData())) {
            $view->setData($this->data);
        }
        if ($view instanceof DIObjectInterface) {
            $view->inject($this->container);
        }
        return $view;
    }
    
    /**
     * Create object for each widgets data item.
     * @param array $widgetsData
     * @return array
     */
    public function composeWidgets(array $widgetsData): array
    {
        $container = clone $this->container;
        unset($container->widget);
        unset($container->widgets);
        unset($container->model);
        
        $widgetsResults = [];
        
        foreach ($widgetsData as $place => $placeWidgets) {
            $results = [];
            
            foreach ($placeWidgets as $widget) {
                if (
                    !($widget instanceof Widget) 
                    || empty($widget->vendor) 
                    || (empty($widget->module) && empty($widget->name))
                ) {
                    continue;
                }
                
                if (empty($widget->module)) {
                    $widgetControllerClass = 
                        '\Ufo\Modules' . 
                        '\\' . ucfirst($widget->vendor) . 
                        '\Widgets' . 
                        '\\' . ucfirst($widget->name) . 
                        '\Controller';
                } else {
                    $widgetControllerClass = 
                        '\Ufo\Modules' . 
                        '\\' . ucfirst($widget->vendor) . 
                        '\\' . ucfirst($widget->module) . 
                        '\Widget' . ucfirst($widget->name) . 'Controller';
                }
                
                $container->set('widget', $widget);
                $container->set('data', get_object_vars($widget));
                
                if (class_exists($widgetControllerClass)) {
                    $widgetController = new $widgetControllerClass();
                    if ($widgetController instanceof DIObjectInterface) {
                        $widgetController->inject($container);
                    }
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
     * @throws \Ufo\Core\ModuleParameterConflictException
     * @throws \Ufo\Core\ModuleParameterFormatException;
     * @throws \Ufo\Core\ModuleParameterUnknownException
     */
    protected function setParamsFromPath(array $pathParams): void
    {
        foreach ($pathParams as $pathParam) {
            $this->setParamFromPath($pathParam);
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
            $this->setParamAssigned($paramSet);
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
     * @return void
     * @throws \Ufo\Core\ModuleParameterConflictException
     * @throws \Ufo\Core\ModuleParameterFormatException;
     * @throws \Ufo\Core\ModuleParameterUnknownException
     */
    protected function setParamFromPath(string $pathParam): void
    {
        foreach ($this->params as $paramName => $paramSet) {
            if ('path' != $paramSet->from || in_array($paramName, $this->paramsAssigned)) {
                continue;
            }
            
            if ('' != $paramSet->prefix) { //for named params
                if (0 !== strpos($pathParam, $paramSet->prefix)) {
                    continue;
                }
                if ($this->isParamAssigned($paramSet)) {
                    throw new ModuleParameterConflictException();
                }
                $val = substr($pathParam, strlen($paramSet->prefix));
                switch ($paramSet->type) {
                    case 'int':
                        $this->params[$paramName]->value = (int) $val;
                        break;
                    case 'bool':
                        $this->params[$paramName]->value = true;
                        break;
                    case 'date':
                        $date = strtotime($val);
                        if (false === $date) {
                            throw new ModuleParameterFormatException();
                        }
                        $this->params[$paramName]->value = $date;
                        break;
                    default:
                        $this->params[$paramName]->value = $val;
                }
                $this->setParamAssigned($paramSet);
                return;
                
            } elseif ('int' == $paramSet->type && Tools::isInt($pathParam)) { //digits only, itemId for example
                if ($this->isParamAssigned($paramSet)) {
                    throw new ModuleParameterConflictException();
                }
                $this->params[$paramName]->value = (int) $pathParam;
                $this->setParamAssigned($paramSet);
                return;
                
            //for now used only exactly 10 symbols dates formats, like YYYY-MM-DD
            } elseif ('date' == $paramSet->type && 10 == strlen($pathParam) && false !== strtotime($pathParam)) { //for dates
                if ($this->isParamAssigned($paramSet)) {
                    throw new ModuleParameterConflictException();
                }
                $this->params[$paramName]->value = strtotime($pathParam);
                $this->setParamAssigned($paramSet);
                return;
                
            } elseif ('' == $paramSet->prefix && 'string' == $paramSet->type && null === $paramSet->value) { //for params without prefix
                if ($this->isParamAssigned($paramSet)) {
                    throw new ModuleParameterConflictException();
                }
                $this->params[$paramName]->value = $pathParam;
                $this->setParamAssigned($paramSet);
                return;
            }
            
            //TODO: implement user defined types parameters extraction
        }
        
        throw new ModuleParameterUnknownException();
    }
    
    /**
     * @param \Ufo\Modules\Parameter
     * @return bool
     */
    protected function isParamAssigned(Parameter $param): bool
    {
        //in case of more than one come parameters
        //(for example item id and some date) generate 404 error
        //for not additional parameters, because two (or more)
        //not additional parameters may give ambiguity
        // /section/id123/dt2017 | /section/dt2017/id123
        return !$param->additional && in_array('all', $this->paramsAssigned);
    }
    
    /**
     * @param \Ufo\Modules\Parameter
     * @return void
     */
    protected function setParamAssigned(Parameter $param): void
    {
        if ($param->additional) {
            $this->paramsAssigned[] = $param->name;
        } else {
            $this->paramsAssigned[] = 'all';
        }
    }
}
