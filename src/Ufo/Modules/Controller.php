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
use Ufo\Core\Result;
use Ufo\Core\Section;

/**
 * Module level controller base class.
 */
class Controller extends DIObject implements ControllerInterface
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
     * @param ContainerInterface $container
     */
    public function inject(ContainerInterface $container): void
    {
        parent::inject($container);
        $this->unpackContainer();
    }
    
    /**
     * Main controller method.
     * @return Result
     */
    public function execute(): Result
    {
        return new Result(__METHOD__ . PHP_EOL . print_r($this->section, true));
    }
}
