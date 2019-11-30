<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

use PhpStrict\Struct\Struct;

/**
 * Configuration class.
 */
class Config extends Struct implements ConfigInterface
{
    public const STORAGE_TYPE_ARRAY = 'array';
    public const STORAGE_TYPE_DB = 'db';
    
    public const CACHE_TYPE_ARRAY = 'array';
    public const CACHE_TYPE_FILES = 'files';
    public const CACHE_TYPE_MYSQL = 'mysql';
    public const CACHE_TYPE_SQLITE = 'sqlite';
    public const CACHE_TYPE_REDIS = 'redis';
    public const CACHE_TYPE_MEMCACHED = 'memcached';
    
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
     * Type of widgets storage.
     * @var string STORAGE_TYPE_*
     */
    public $widgetsStorageType = 'array';
    
    /**
     * Path to file with array of widgets (for array type storage).
     * @var string
     */
    public $widgetsStoragePath = '';
    
    /**
     * Array of widgets (for array type storage).
     * @var array
     */
    public $widgetsStorageData = [];
    
    /**
     * Cache enabled flag.
     * @var bool
     */
    public $cache = false;
    
    /**
     * Cache storage type.
     * @var string CACHE_TYPE_*
     */
    public $cacheType = 'files';
    
    /**
     * Cache TTL for whole page content.
     * @var int
     */
    public $cacheTtlWholePage = 3;
    
    /**
     * Cache TTS for whole page content.
     * @var int
     */
    public $cacheTtsWholePage = 86400;
    
    /**
     * Cache TTL for widgets.
     * @var int
     */
    public $cacheTtlWidget = 30;
    
    /**
     * Path to cache dir.
     * @var string
     */
    public $cacheDir = '/cache';
    
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
     * Path to project root (usually its parent for site root).
     */
    public $projectPath = '';
    
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
        $this->projectPath = dirname($_SERVER['DOCUMENT_ROOT']);
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
        $config = include $configPath;
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
        $configDefault = include $defaultConfigPath;
        $config = include $configPath;
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
        
        $iniArr = null;
        try {
            $iniArr = parse_ini_file($iniPath, false, INI_SCANNER_TYPED);
        } catch (\Throwable $e) {
        }
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
