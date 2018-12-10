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
 * Configuration interface.
 */
interface ConfigInterface extends StructInterface
{
    /**
     * Loads configuration from configuration file.
     * @param string $configPath
     * @param bool $overwrite = false
     */
    public function load(string $configPath, bool $overwrite = false): void;
    
    /**
     * Loads configuration from configuration file, and from default configuration file.
     * @param string $defaultConfigPath
     * @param string $configPath
     */
    public function loadWithDefault(string $configPath, string $defaultConfigPath): void;
    
    /**
     * Loads configuration from array.
     * @param array $config
     * @param bool $overwrite = false
     */
    public function loadArray(array $config, bool $overwrite = false): void;
}
