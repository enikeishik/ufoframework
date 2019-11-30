<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

use PhpStrict\Struct\Struct;

class Widget extends Struct
{
    /**
     * Author of widget.
     * @var string
     */
    public $vendor = '';
    
    /**
     * Module name for modules widgets, empty for own widgets.
     * @var string
     */
    public $module = '';
    
    /**
     * Widget name, may be empty for single (main) widget.
     * @var string
     */
    public $name = '';
    
    /**
     * Widget NOT require db connection.
     * @var bool
     */
    public $dbless = true;
    
    /**
     * Source sections (ids), if widget gets content from sections.
     * @var array
     */
    public $sourceSections = [];
    
    /**
     * Show widgets title.
     * @var bool
     */
    public $showTitle = true;
    
    /**
     * Widget title.
     * @var string
     */
    public $title = '';
    
    /**
     * Widget content.
     * @var string
     */
    public $text = '';
    
    /**
     * Widget level parameters.
     * @var array
     */
    public $params = [];
}
