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
 * Dependency injection object interface.
 */
interface DIObjectInterface
{
    /**
     * @param \Ufo\Core\ContainerInterface $container
     * @return void
     */
    public function inject(ContainerInterface $container): void;
}
