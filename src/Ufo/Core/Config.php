<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

use PhpStrict\Config\Config as AbstractConfig;

/**
 * Configuration class.
 */
class Config extends AbstractConfig
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
     * Routes table name (for DB type storage).
     * @var array
     */
    public $routeStorageTable = 'routes';
    
    /**
     * Routes table key field (for DB type storage).
     * @var array
     */
    public $routeStorageKeyField = 'key';
    
    /**
     * Routes table data field (for DB type storage).
     * @var array
     */
    public $routeStorageDataField = 'data';
    
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
    public function __construct(array $config = [])
    {
        $this->rootUrl = $this->rootPath;
        $this->rootPath = $_SERVER['DOCUMENT_ROOT'] . $this->rootPath;
        $this->projectPath = dirname($_SERVER['DOCUMENT_ROOT']);
        parent::__construct($config);
    }
}
