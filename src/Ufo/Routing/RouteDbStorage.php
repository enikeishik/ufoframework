<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Routing;

use Ufo\Core\Db;
use Ufo\Core\Section;

class RouteDbStorage extends RouteStorage
{
    /**
     * @var Db
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
        $item = $this->db->getItem($sql);
        if (null === $item) {
            return null;
        }
        return $this->getSection($path, $item);
    }
    
    public function get(string $path): ?Section
    {
        $sql = 'SELECT m.id, m.disabled, m.name FROM #__sections AS s' . 
               ' INNER JOIN #__modules AS m ON s.module_id = m.id' . 
               " WHERE s.path ='" . $path . "'";
        $item = $this->db->getItem($sql);
        if (null === $item) {
            return null;
        }
        return $this->getSection($path, $item);
    }
    
    protected function getSection(string $path, array $item): Section
    {
        $item['dbless'] => false;
        $item['callback'] = ''; //to prevent hack by SQL injection
        
        return new Section(array_merge(['path' => $path], $item));
    }
}
