<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Modules;

/**
 * Module level renderable class.
 */
class Renderable implements RenderableInterface
{
    /**
     * @var string
     */
    protected $content = '';
    
    /**
     * @param string $content
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }
    
    /**
     * Render.
     * @return string
     */
    public function render(): string
    {
        return $this->content;
    }
}
