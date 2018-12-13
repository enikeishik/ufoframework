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
 * Dependency injection base object.
 */
abstract class DIObject implements DIObjectInterface
{
    /**
     * @var \Ufo\Core\ContainerInterface
     */
    protected $container = null;
    
    /**
     * @param \Ufo\Core\ContainerInterface $container
     */
    public function inject(ContainerInterface $container): void
    {
        $this->container = $container;
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
