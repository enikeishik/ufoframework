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
 * Module level model base class.
 */
class Model extends DIObject implements ModelInterface
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
     * Module parameters coming from URL|GET|POST|COOKIE.
     * @var array
     */
    protected $params = [];
}
