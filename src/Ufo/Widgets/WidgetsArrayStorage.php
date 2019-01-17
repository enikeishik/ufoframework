<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Widgets;

use Ufo\Core\Section;

class WidgetsArrayStorage extends WidgetsStorage
{
    /**
     * @var array
     */
    protected $storage = [];
    
    /**
     * @param array $storage
     */
    public function __construct(array $storage)
    {
        $this->storage = $storage;
        
        if (0 < count($this->storage)) {
            krsort($this->storage, SORT_STRING);
        }
    }
    
    /**
     * @param \Ufo\Core\Section $section
     * @return array
     */
    public function getWidgets(Section $section): array
    {
        if (array_key_exists($section->path, $this->storage)) {
            if (array_key_exists('', $this->storage)) {
                return array_merge_recursive($this->storage[''], $this->storage[$section->path]);
            } else {
                return $this->storage[$section->path];
            }
        } elseif (array_key_exists('', $this->storage)) {
            return $this->storage[''];
        }
        
        return [];
    }
}
