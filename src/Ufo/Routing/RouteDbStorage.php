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
     * @var \Ufo\Core\Db
     */
    protected $db;
    
    /**
     * @param \Ufo\Core\Db $db
     * @throws \Ufo\Routing\RouteStorageEmptyException
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
        
        $sql = 'SELECT COUNT(*) AS cnt FROM #__sections';
        if (empty($this->db->getValue($sql, 'cnt'))) {
            throw new RouteStorageEmptyException();
        }
    }
    
    /**
     * @param string $path
     * @return ?\Ufo\Core\Section
     */
    public function find(string $path): ?Section
    {
        $sql =  'SELECT s.title, m.vendor, m.name, m.disabled' . 
                ' FROM #__sections AS s' . 
                ' INNER JOIN #__modules AS m ON s.module = m.package' . 
                " WHERE s.path IN('" . implode("','", $this->getPaths($path)) . "')" . 
                ' ORDER BY s.path DESC' . 
                ' LIMIT 1';
        $item = $this->db->getItem($sql);
        if (null === $item) {
            return null;
        }
        return $this->getSection($path, $item);
    }
    
    /**
     * @param string $path
     * @return ?\Ufo\Core\Section
     */
    public function get(string $path): ?Section
    {
        $sql =  'SELECT s.title, m.vendor, m.name, m.disabled' . 
                ' FROM #__sections AS s' . 
                ' INNER JOIN #__modules AS m ON s.module = m.package' . 
                " WHERE s.path ='" . $path . "'";
        $item = $this->db->getItem($sql);
        if (null === $item) {
            return null;
        }
        return $this->getSection($path, $item);
    }
    
    /**
     * @param string $path
     * @param array $item
     * @return \Ufo\Core\Section
     */
    protected function getSection(string $path, array $item): Section
    {
        $module = $this->getModule(array_merge(
            $item, 
            [
                'callback' => '', //to prevent hack by SQL injection
                'dbless' => false, 
            ]
        ));
        
        return new Section([
            'path' => $path, 
            'title' => $item['title'], 
            'module' => $module, 
        ]);
    }
}
