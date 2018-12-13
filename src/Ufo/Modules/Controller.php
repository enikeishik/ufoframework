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
use Ufo\Core\DebugInterface;
use Ufo\Core\DIObject;
use Ufo\Core\ContainerInterface;
use Ufo\Core\Result;
use Ufo\Core\Section;

/**
 * Module level controller base class.
 */
class Controller extends DIObject implements ControllerInterface
{
    /**
     * @var \Ufo\Core\Config
     */
    protected $config;
    
    /**
     * @var \Ufo\Core\DebugInterface
     */
    protected $debug;
    
    /**
     * @param \Ufo\Core\ContainerInterface $container
     */
    public function inject(ContainerInterface $container): void
    {
        parent::inject($container);
        $this->unpackContainer();
    }
    
    /**
     * Main controller method, compose all content.
     * @param \Ufo\Core\Section $section
     * @return \Ufo\Core\Result
     */
    public function compose(Section $section): Result
    {
        $this->container->set('section', $section);
        
        $model = new Model();
        $model->inject($this->container);
        
        $view = new View();
        $this->container->set('model', $model);
        //lazy render
        //$this->container->set('widgets', $this->getWidgets($section));
        //prerender
        $this->container->set('widgets', $this->composeWidgets($this->getWidgets($section)));
        $view->inject($this->container);
        
        $context = [
            'info'      => __METHOD__ . PHP_EOL . print_r($section, true), 
            'items'     => $model->getItems(), 
        ];
        
        return new Result($view->render('view', $context));
    }
    
    /**
     * Returns widgets data grouped by place (place is a key).
     * @param \Ufo\Core\Section $section
     * @return array
     */
    public function getWidgets(Section $section): array
    {
        return [
            'left col top' => [
                ['module' => '', 'name' => 'gismeteo', 'title' => '1 first wdg title', 'text' => '1 first wdg text'], 
                ['module' => 'news', 'name' => '', 'title' => '1 second wdg title', 'text' => '1 second wdg text'], 
            ], 
            'right col bottom' => [
                ['module' => 'gallery', 'name' => '', 'title' => '2 first wdg title', 'text' => '2 first wdg text'], 
            ], 
        ];
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
            foreach ($placeWidgets as $widget) {
                if (empty($widget['module'])) {
                    $class = 
                        '\Ufo\Modules\Widgets\\' . 
                        ucfirst($widget['name']) . '\Controller';
                } else {
                    $class = 
                        '\Ufo\Modules\\' . ucfirst($widget['module']) . '\\' . 
                        'Widget' . ucfirst($widget['name']) . 'Controller';
                }
                if (class_exists($class)) {
                    $wdt = new $class();
                    $wdt->inject($container);
                    $widgetsResults[$place] = $wdt->compose($widgetSection);
                }
            }
        }
        
        return $widgetsResults;
    }
}
