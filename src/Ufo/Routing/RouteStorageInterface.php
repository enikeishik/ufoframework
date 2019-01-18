<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Routing;

use Ufo\Core\Section;

interface RouteStorageInterface
{
    /**
     * @param string $path
     * @return ?\Ufo\Core\Section
     */
    public function get(string $path): ?Section;
    
    /**
     * @param string $path
     * @return ?\Ufo\Core\Section
     */
    public function find(string $path): ?Section;
}
