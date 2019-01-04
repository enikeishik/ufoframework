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

/**
 * Route application class.
 */
class Route
{
    public static function parse(string $path, RouteStorageInterface $routeStorage): ?Section
    {
        if (empty($path) || '/' == $path) {
            $section = $routeStorage->get('/');
        } else {
            $section = $routeStorage->find($path);
        }
        
        if (null === $section) {
            return null;
        }
        
        //strlen faster than simple comparison
        $splen = strlen($section->path);
        if (strlen($path) > $splen) {
            $section->params = explode('/', trim(substr($path, $splen), '/'));
        }
        
        return $section;
    }
}
