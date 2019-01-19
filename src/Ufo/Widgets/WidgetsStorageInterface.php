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

interface WidgetsStorageInterface
{
    /**
     * @param \Ufo\Core\Section $section
     * @return array
     * 
     * return array structure:
     * [
     *      'place 1 name' => [
     *          Widget $widget1, 
     *          Widget $widget2, 
     *          ...
     *      ], 
     *      'place 2 name' => [
     *          ...
     *      ], 
     *      ...
     * ]
     */
    public function getWidgets(Section $section): array;
}
