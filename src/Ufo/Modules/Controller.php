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
        $view->inject($this->container);
        
        $context = [
            'info'      => __METHOD__ . PHP_EOL . print_r($section, true), 
            'items'     => $model->getItems(), 
            'widgets'   => $this->composeWidgets($section), 
        ];
        
        return new Result($view->render('view', $context));
    }
    
    /**
     * Compose widgets data.
     * @param \Ufo\Core\Section $section
     * @return array
     */
    public function composeWidgets(Section $section): array
    {
        return [
            'left col top' => [
                ['title' => '1 first wdg title', 'text' => '1 first wdg text'], 
                ['title' => '1 second wdg title', 'text' => '1 second wdg text'], 
            ], 
            'right colbottom' => [
                ['title' => '2 first wdg title', 'text' => '2 first wdg text'], 
            ], 
        ];
    }
}
