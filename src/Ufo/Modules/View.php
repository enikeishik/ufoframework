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
use Ufo\Core\Section;
use Ufo\Core\Result;

/**
 * Module level view base class.
 */
class View extends DIObject implements ViewInterface
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
     * @var \Ufo\Core\Section
     */
    protected $section;
    
    /**
     * @var string
     */
    protected $view = '';
    
    /**
     * @var array
     */
    protected $data = [];
    
    /**
     * @var array
     */
    protected $widgets = [];
    
    /**
     * @var string
     */
    protected $extension = '.php';
    
    /**
     * @param string $view
     * @param array $data = []
     */
    public function __construct(string $view, array $data = [])
    {
        $this->view = $view;
        $this->data = $data;
    }
    
    /**
     * @param \Ufo\Core\ContainerInterface $container
     */
    public function inject(ContainerInterface $container): void
    {
        parent::inject($container);
        $this->unpackContainer();
    }
    
    /**
     * @param string $view
     * @return void
     */
    public function setView(string $view): void
    {
        $this->view = $view;
    }
    
    /**
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }
    
    /**
     * @param array $data
     * @return void
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }
    
    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Generate output.
     * @return string
     */
    public function render(): string
    {
        $obLevel = ob_get_level();
        ob_start();
        
        extract($this->data);
        
        try {
            include $this->findView(
                $this->config->rootPath . $this->config->viewsPath, 
                $this->section->module->name ?? '', 
                $this->view
            );
        } catch (Exception $e) {
            $this->handleRenderException($e, $obLevel);
        }
        
        return ob_get_clean();
    }
    
    protected function renderWidgets(string $place): string
    {
        if (!array_key_exists($place, $this->widgets)) {
            return '';
        }
        
        //TODO: move it into controller
        $view = new View('widgets', ['widgets' => $this->widgets[$place]]);
        $view->inject($this->container);
        return $view->render();
    }
    
    protected function renderWidget(Result $widget): string
    {
        return $widget->getView()->render();
    }
    
    /**
     * Find full path for requested view. Returned path may not exists.
     * @param string $viewsPath
     * @param string $moduleName
     * @param string $viewName
     * @return string
     */
    protected function findView(string $viewsPath, string $moduleName, string $viewName): string
    {
        if (!empty($moduleName)) {
            // /views/module/views.php
            $view = 
                $viewsPath . 
                '/' . strtolower($moduleName) . 
                '/' . str_replace('.', '/', $viewName) . $this->extension;
            if (file_exists($view)) {
                return $view;
            }
        }
        
        // /views/default/views.php
        $view = 
            $viewsPath . 
            $this->config->viewsDefault . 
            '/' . str_replace('.', '/', $viewName) . $this->extension;
        return $view;
    }
    
    /**
     * @param \Exception $e
     * @param int $obLevel
     * @return void
     * @throws \Exception
     */
    protected function handleRenderException(Exception $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }
        
        throw $e;
    }
}
