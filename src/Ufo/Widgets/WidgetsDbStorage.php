<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Widgets;

use Ufo\Core\Db;
use Ufo\Core\Section;

class WidgetsDbStorage extends WidgetsStorage
{
    /**
     * @var Db
     */
    protected $db;
    
    public function __construct(Db $db)
    {
        $this->db = $db;
    }
    
    public function getWidgets(Section $section): array
    {
        return [];
    }
}
