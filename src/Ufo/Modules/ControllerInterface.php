<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Modules;

use Ufo\Core\Result;
use Ufo\Core\Section;

/**
 * Module level controller base class interface
 */
interface ControllerInterface
{
    /**
     * Main controller method.
     * @param Section $section
     * @return Result
     */
    public function compose(Section $section): Result;
}
