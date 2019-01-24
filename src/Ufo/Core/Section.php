<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

class Section extends Struct
{
    /**
     * @var string
     */
    public $path = '';
    
    /**
     * @var string
     */
    public $title = '';
    
    /**
     * @var array
     */
    public $params = [];
    
    /**
     * @var \Ufo\Core\Module
     */
    public $module = null;
    
    /**
     * @var bool
     */
    public $system = false;
    
    /**
     * @var bool
     */
    public $disabled = false;
    
    public function __construct($vars = null, bool $cast = true)
    {
        parent::__construct($vars, $cast);
        
        if (is_array($this->module)) {
            $this->module = new Module($this->module);
        }
    }
}
