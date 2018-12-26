<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

class Module extends Struct
{
    /**
     * @var int
     */
    public $id = 0;
    
    /**
     * @var string
     */
    public $name = '';
    
    /**
     * @var callable
     */
    public $callback = null;
    
    /**
     * @var bool
     */
    public $dbless = false;
    
    /**
     * @var bool
     */
    public $disabled = false;
}
