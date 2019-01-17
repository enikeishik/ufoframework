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
use Ufo\Core\Widget;

class WidgetsDbStorage extends WidgetsStorage
{
    /**
     * @var \Ufo\Core\Db
     */
    protected $db;
    
    /**
     * @param \Ufo\Core\Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }
    
    /**
     * @param \Ufo\Core\Section $section
     * @return array
     */
    public function getWidgets(Section $section): array
    {
        $sql =  'SELECT w.place, w.widget' . 
                ' FROM #__widgets AS w' . 
                ' LEFT JOIN #__sections AS s ON w.section_id = s.id' . 
                ' WHERE w.disabled=0' . 
                " AND (s.path ='" . $section->path . "' OR s.path IS NULL)" . 
                ' ORDER BY s.path, w.order_id';
        $items = $this->db->getItems($sql);
        if (null === $items) {
            return [];
        }
        
        $widgets = [];
        foreach ($items as $item) {
            $widgets[$item['place']][] = new Widget(json_decode($item['widget']));
        }
        return $widgets;
    }
}
