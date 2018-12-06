<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Routing;

abstract class RouteStorage implements RouteStorageInterface
{
    /**
     * Split path and create array of paths with all parts.
     * "/qwe/asd/zxc" -> ["/qwe/asd/zxc", "/qwe/asd", "/qwe"]
     * 
     * @param string $path
     * @return array
     */
    protected function getPaths(string $path): array
    {
        $pathParts = explode('/', trim($path, '/'));
        $pathPartsCount = count($pathParts);
        //if ($this->config->pathNestingLimit < $pathPartsCount) {
            //error
        //}
        
        $paths = array('');
        for ($i = 0; $i < $pathPartsCount; $i++) {
            $paths[$i + 1] = $paths[$i] . '/' . $pathParts[$i];
        }
        array_shift($paths);
        
        rsort($paths, SORT_STRING);
        
        return $paths;
    }
}
