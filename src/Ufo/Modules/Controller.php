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
use Ufo\Core\Result;
use Ufo\Core\Section;
use \Ufo\Modules\View;

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
     * @var array
     */
    protected $data = [];
    
    /**
     * Main controller method, compose all content.
     * @param \Ufo\Core\Section $section = null
     * @return \Ufo\Core\Result
     */
    public function compose(Section $section = null): Result
    {
        $this->container->set('section', $section);
        
        if (0 == count($this->data)) {
            $model = new Model();
            $model->inject($this->container);
            
            $this->container->set('model', $model);
            if (null !== $section) {
                $this->container->set('widgets', $this->composeWidgets($this->getWidgets($section)));
            }
            
            $this->data = [
                'info'      => __METHOD__ . PHP_EOL . print_r($section, true), 
                'title'     => $section->title, 
                'items'     => $model->getItems(), 
            ];
        }
        
        $view = new View($this->config->templateDefault, $this->data);
        $view->inject($this->container);
        
        return new Result($view);
    }
    
    /**
     * Returns widgets data grouped by place (place is a key).
     * @param \Ufo\Core\Section $section
     * @return array
     */
    public function getWidgets(Section $section): array
    {
        return $this->app->getWidgets($section);
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
}
