<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo;

class Module extends Struct
{
    public $id = 0;
    public $name = '';
    public $controller = null;
    public $model = null;
    public $view = null;
    public $system = false;
    public $disabled = false;
    public $error = null;
}
