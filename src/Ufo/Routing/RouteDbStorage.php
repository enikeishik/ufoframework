<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Routing;

use Ufo\Section;

class RouteDbStorage extends RouteStorage
{
    /**
     * @var Ufo\Db
     */
    protected $db;
    
    public function __construct(Db $db)
    {
        $this->db = $db;
    }
    
    public function find(string $path): ?Section
    {
        $sql = 'SELECT m.id, m.disabled, m.name FROM #__sections AS s' . 
               ' INNER JOIN #__modules AS m ON s.module_id = m.id' . 
               " WHERE s.path IN('" . implode("','", $paths) . "')" . 
               ' ORDER BY s.path DESC' . 
               ' LIMIT 1';
        return $this->db->getItem($sql);
    }
    
    public function get(string $path): ?Section
    {
        $sql = 'SELECT m.id, m.disabled, m.name FROM #__sections AS s' . 
               ' INNER JOIN #__modules AS m ON s.module_id = m.id' . 
               " WHERE s.path ='" . $path . "'";
        return $this->db->getItem($sql);
    }
}
