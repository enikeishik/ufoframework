<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Modules;

use Ufo\Core\Module;
use Ufo\Core\Widget;

/**
 * Module level service provider class.
 */
abstract class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Must be defined in child class to gets it's own __DIR__.
     * @var string
     */
    protected static $dir = __DIR__;
    
    /**
     * @var object
     */
    protected $schema = null;
    
    /**
     * @var \Ufo\Core\Module
     */
    protected $module = null;
    
    /**
     * @return \Ufo\Core\Module
     * @throws \Exception
     */
    public function getModule(): Module
    {
        if (null !== $this->module) {
            return $this->module;
        }
        
        $moduleSchema = $this->getModuleSchema();
        $moduleSchema->dbless = !$this->isSqlDumpExists();
        $this->module = new Module($moduleSchema);
        
        return $this->module;
    }
    
    /**
     * @return array<\Ufo\Core\Widget>
     */
    public function getWidgets(): array
    {
        $widgets = [];
        foreach (glob($this->getWidgetsPath()) as $path) {
            $widgetData = @include $path;
            if (is_array($widgetData)) {
                $widgets[] = new Widget($widgetData);
            }
        }
        return $widgets;
    }
    
    /**
     * @return ?string
     * @throws \Exception
     */
    public function getSqlDump(): ?string
    {
        if (!$this->isSqlDumpExists()) {
            return null;
        }
        
        $sql = @file_get_contents($this->getSqlDumpPath());
        if (false === $sql) {
            throw new \Exception('Read SQL dump failed');
        }
        
        return $sql;
    }
    
    /**
     * @return string
     */
    protected function getModulePath(): string
    {
        return $this->getRootPath() . '/composer.json';
    }
    
    /**
     * @return object
     * @throws \Exception
     */
    protected function getModuleSchema(): object
    {
        if (null !== $this->schema) {
            return $this->schema;
        }
        
        $json = @file_get_contents($this->getModulePath());
        if (false === $json) {
            throw new \Exception('Read schema failed');
        }
        
        $schema = json_decode($json);
        if (!isset($schema->name)) {
            throw new \Exception('Schema name field not exists or bad JSON');
        }
        
        $vn = explode('/', $schema->name);
        if (2 != count($vn)) {
            throw new \Exception('Schema name field not correct');
        }
        $schema->vendor = $vn[0];
        $schema->name = $vn[1];
        
        $this->schema = $schema;
        
        return $this->schema;
    }
    
    /**
     * @return string
     */
    protected function getWidgetsPath(): string
    {
        return $this->getRootPath() . '/data/widget*.php';
    }
    
    /**
     * @return bool
     */
    protected function isSqlDumpExists(): bool
    {
        return file_exists($this->getSqlDumpPath());
    }
    
    /**
     * @return string
     */
    protected function getSqlDumpPath(): string
    {
        return $this->getRootPath() . '/data/' . $this->getModuleSchema()->name . '.sql';
    }
    
    /**
     * Must be overriden if service provider placed in another location.
     * @return string
     */
    protected function getRootPath(): string
    {
        return static::$dir . '/..';
    }
}
