<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

use PhpStrict\Container\ContainerInterface;

/**
 * Dependency injection base object.
 */
abstract class DIObject implements DIObjectInterface
{
    /**
     * @var \PhpStrict\Container\ContainerInterface
     */
    protected $container = null;
    
    /**
     * @param \PhpStrict\Container\ContainerInterface $container
     * @param bool $dontUnpack = false
     * @return void
     */
    public function inject(ContainerInterface $container, bool $dontUnpack = false): void
    {
        $this->container = $container;
        if (!$dontUnpack) {
            $this->unpackContainer();
        }
    }
    
    /**
     * @return void
     */
    protected function unpackContainer(): void
    {
        foreach ($this->container->getAll() as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }
}
