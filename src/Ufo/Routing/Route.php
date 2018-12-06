<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Routing;

use Ufo\Section;

/**
 * Route application class.
 */
class Route
{
    public static function parse(string $path, RouteStorageInterface $routeStorage): ?Section
    {
        if (empty($path) || '/' == $path) {
            return $routeStorage->get('/');
        }
        
        return $routeStorage->find($path);
    }
}
