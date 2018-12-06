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
 * Main application class.
 */
class App
{
    /**
     * @var ConfigInterface
     */
    protected $config = null;
    
    /**
     * @var DebugInterface
     */
    protected $debug = null;
    
    public function __construct(ConfigInterface $config, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
    }
}
