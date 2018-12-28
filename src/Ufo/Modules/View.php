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
 * Module level View base class.
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
    protected $template = '';
    
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
     * @param string $template
     * @param array $data = []
     */
    public function __construct(string $template, array $data = [])
    {
        $this->template = $template;
        $this->data = $data;
    }
    
    /**
     * @param string $template
     * @return void
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }
    
    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }
    
    /**
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
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
     * @param array $data
     * @return void
     */
    public function setWidgets(array $widgets): void
    {
        $this->widgets = $widgets;
    }
    
    /**
     * @return array
     */
    public function getWidgets(): array
    {
        return $this->widgets;
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
                $this->config->projectPath . $this->config->templatesPath, 
                $this->section->module->name ?? '', 
                $this->template
            );
        } catch (\Exception $e) {
            $this->handleRenderException($e, $obLevel);
        }
        
        return ob_get_clean();
    }
    
    /**
     * @param string $place
     * @return string
     */
    protected function renderWidgets(string $place): string
    {
        if (array_key_exists($place, $this->widgets)) {
            return $this->widgets[$place]->render();
        }
        
        return '';
    }
    
    /**
     * @param Result $widget
     * @return string
     */
    protected function renderWidget(Result $widget): string
    {
        return $widget->getView()->render();
    }
    
    /**
     * Find full path for requested template. Returned path may not exists.
     * @param string $templatesPath
     * @param string $moduleName
     * @param string $templateName
     * @return string
     */
    protected function findView(string $templatesPath, string $moduleName, string $templateName): string
    {
        if (!empty($moduleName)) {
            // /templates/module/template.php
            $templatePath = 
                $templatesPath . 
                '/' . strtolower($moduleName) . 
                '/' . str_replace('.', '/', $templateName) . $this->extension;
            if (file_exists($templatePath)) {
                return $templatePath;
            }
        }
        
        // /templates/default/template.php
        $templatePath = 
            $templatesPath . 
            $this->config->templatesDefault . 
            '/' . str_replace('.', '/', $templateName) . $this->extension;
        return $templatePath;
    }
    
    /**
     * @param \Exception $e
     * @param int $obLevel
     * @return void
     * @throws \Exception
     */
    protected function handleRenderException(\Exception $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }
        
        throw $e;
    }
}
