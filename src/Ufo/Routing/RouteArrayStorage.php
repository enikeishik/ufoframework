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

class RouteArrayStorage extends RouteStorage
{
    /**
     * @var array
     */
    protected $storage = [];
    
    /**
     * @param array $storage
     * @throws \Ufo\Routing\RouteStorageEmptyException
     */
    public function __construct(array $storage)
    {
        $this->storage = $storage;
        
        if (0 == count($this->storage)) {
            throw new RouteStorageEmptyException();
        }
        krsort($this->storage, SORT_STRING);
    }
    
    /**
     * @param string $path
     * @return ?\Ufo\Core\Section
     */
    public function find(string $path): ?Section
    {
        $paths = $this->getPaths($path);
        foreach ($paths as $path) {
            $section = $this->get($path);
            if (null !== $section) {
                return $section;
            }
        }
        
        return null;
    }
    
    /**
     * @param string $path
     * @return ?\Ufo\Core\Section
     */
    public function get(string $path): ?Section
    {
        if (array_key_exists($path, $this->storage)) {
            $this->storage[$path]['module'] = $this->getModule($this->storage[$path]['module']);
            return new Section(
                array_merge(
                    ['path' => $path], 
                    $this->storage[$path]
                )
            );
        }
        
        return null;
    }
}
