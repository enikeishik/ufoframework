<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

/**
 * Класс конфигурации.
 */
class Config extends Struct implements ConfigInterface
{
    public const STORAGE_TYPE_ARRAY = 'array';
    public const STORAGE_TYPE_DB = 'db';
    
    /**
     * Type of routes storage.
     * @var string 'array', 'db'
     */
    public $routeStorageType = 'array';
    
    /**
     * Path to file with array of routes (for array type storage).
     * @var string
     */
    public $routeStoragePath = '';
    
    /**
     * Array of routes (for array type storage).
     * @var array
     */
    public $routeStorageData = [];
    
    /**
     * Path to site root.
     * @var string
     */
    public $rootPath = '';
    
    /**
     * Path to site root for URL, sets from $rootPath.
     * @var string
     */
    public $rootUrl = '';
    
    /**
     * Path to templates root.
     * @var string
     */
    public $templatesPath = '/resources/templates';
    
    /**
     * Default templates dir.
     * @var string
     */
    public $templatesDefault = '/default';
    
    /**
     * Default template.
     * @var string
     */
    public $templateDefault = 'index';
    
    /**
     * Widgets list template.
     * @var string
     */
    public $templateWidgets = 'widgets';
    
    /**
     * Widget template.
     * @var string
     */
    public $templateWidget = 'widget';
    
    /**
     * @see parent
     */
    public function __construct($vars = null, bool $cast = true)
    {
        $this->rootUrl = $this->rootPath;
        $this->rootPath = $_SERVER['DOCUMENT_ROOT'] . $this->rootPath;
        parent::__construct($vars, $cast);
    }
    
    /**
     * Loads configuration from configuration file.
     * @param string $configPath
     * @param bool $overwrite = false
     */
    public function load(string $configPath, $overwrite = false): void
    {
        if (!file_exists($configPath)) {
            return;
        }
        $config = include_once $configPath;
        if (!is_array($config) && !is_object($config)) {
            return;
        }
        if (is_object($config)) {
            $config = get_object_vars($config);
        }
        $this->loadArray($config, $overwrite);
    }
    
    /**
     * Loads configuration from configuration file, and from default configuration file.
     * @param string $configPath
     * @param string $defaultConfigPath
     */
    public function loadWithDefault(string $configPath, string $defaultConfigPath): void
    {
        if (!file_exists($configPath)) {
            return;
        }
        $configDefault = include_once $defaultConfigPath;
        $config = include_once $configPath;
        if (!is_array($configDefault) && !is_array($config)) {
            return;
        }
        $this->loadArray(array_merge($configDefault, $config));
    }
    
    /**
     * Loads configuration from array.
     * @param array $config
     * @param bool $overwrite = false
     */
    public function loadArray(array $config, $overwrite = false): void
    {
        foreach ($config as $name => $value) {
            if (!$overwrite && property_exists($this, $name)) {
                continue;
            }
            $this->$name = $value;
        }
    }
    
    /**
     * Loads configuration from INI file.
     * @param string $iniPath
     * @param bool $overwrite = false
     */
    public function loadFromIni(string $iniPath, bool $overwrite = false): void
    {
        if (!file_exists($iniPath)) {
            return;
        }
        
        $iniArr = parse_ini_file($iniPath);
        if (!is_array($iniArr)) {
            return;
        }
        $iniArr = array_change_key_case($iniArr, CASE_LOWER);
        
        $arr = [];
        foreach ($iniArr as $name => $value) {
            $arr[lcfirst(str_replace('_', '', ucwords(str_replace('.', '_', $name), '_')))] = $value;
        }
        
        $this->loadArray($arr, $overwrite);
    }
}
