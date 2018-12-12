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

/**
 * Module level view base class.
 */
class View extends DIObject //implements ViewInterface
{
    /**
     * @var Config
     */
    protected $config;
    
    /**
     * @var DebugInterface
     */
    protected $debug;
    
    /**
     * @var Section
     */
    protected $section;
    
    /**
     * @var string
     */
    protected $extension = '.php';
    
    /**
     * @param ContainerInterface $container
     */
    public function inject(ContainerInterface $container): void
    {
        parent::inject($container);
        $this->unpackContainer();
    }
    
    /**
     * Generate output.
     * @param string $template
     * @param array $context
     * @return string
     */
    public function render(string $template, array $context): string
    {
        $obLevel = ob_get_level();
        ob_start();
        
        extract($context);
        
        try {
            include $template;
        } catch (Exception $e) {
            $this->handleRenderException($e, $obLevel);
        }
        
        return ob_get_clean();
    }
    
    /**
     * Find full path for requested template. Returned path may not exists.
     * @param string $templatesPath
     * @param string $moduleName
     * @param string $templateName
     * @return string
     */
    protected function findTemplate(string $templatesPath, string $moduleName, string $templateName): string
    {
        if (!empty($moduleName)) {
            // /templates/module/template.php
            $template = 
                $templatesPath . 
                '/' . strtolower($moduleName) . 
                str_replace('.', '/', $templateName) . $this->extension;
            if (file_exists($template)) {
                return $template;
            }
        }
        
        // /templates/default/template.php
        $template = 
            $templatesPath . 
            $this->config->templatesDefault . 
            str_replace('.', '/', $templateName) . $this->extension;
        return $template;
    }
    
    /**
     * @param Exception $e
     * @param int $obLevel
     * @return void
     * @throws Exception
     */
    protected function handleRenderException(Exception $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }
        
        throw $e;
    }
}
