<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo;

/**
 * Класс конфигурации.
 */
class Config extends Struct implements ConfigInterface
{
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
}
