<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Cache;

use Ufo\Core\Config;
use Ufo\Core\Db;
use Ufo\Core\DebugInterface;

/**
 * Cache database based storage.
 */
class CacheMysqlStorage implements CacheStorageInterface
{
    /**
     * @var \Ufo\Core\Config
     */
    protected $config = null;
    
    /**
     * @var \Ufo\Core\DebugInterface
     */
    protected $debug = null;
    
    /**
     * @var \Ufo\Core\Db
     */
    protected $db = null;
    
    /**
     * @var string
     */
    protected $cacheDbTable = 'cache';
    
    /**
     * @var string
     */
    protected $cacheDbKeyField = 'key';
    
    /**
     * @var string
     */
    protected $cacheDbValueField = 'value';
    
    /**
     * @var string
     */
    protected $cacheDbTimeField = 'dtm';
    
    /**
     * @param \Ufo\Core\Config $config
     * @param \Ufo\Core\Db $db
     * @param \Ufo\Core\DebugInterface $debug = null
     */
    public function __construct(Config $config, Db $db, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        $this->db = $debug;
        $this->cacheDbTable =  $this->config->cacheDbTable;
        $this->cacheDbKeyField =  $this->config->cacheDbKeyField;
        $this->cacheDbValueField =  $this->config->cacheDbValueField;
        $this->cacheDbTimeField =  $this->config->cacheDbTimeField;
    }
    
    /**
     * Determines whether an item is present in the cache.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $sql =  'SELECT COUNT(*) AS Cnt FROM #__' . $this->cacheDbTable . 
                " WHERE `" . $this->cacheDbKeyField . "`='" . $this->db->addEscape($key) . "'";
        $cnt = $this->db->getValue($sql, 'Cnt');
        if (null === $cnt) {
            return false;
        }
        return 0 < (int) $cnt;
    }
    
    /**
     * Fetches a value from the cache.
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $sql =  'SELECT `' . $this->cacheDbValueField . '` FROM #__' . $this->cacheDbTable . 
                " WHERE `" . $this->cacheDbKeyField . "`='" . $this->db->addEscape($key) . "'";
        return $this->db->getValue($sql, $this->cacheDbValueField);
    }
    
    /**
     * Fetches a value age from the cache.
     * @param string $key
     * @return int
     */
    public function getAge(string $key): int
    {
        $sql =  'SELECT `' . $this->cacheDbTimeField . '` FROM #__' . $this->cacheDbTable . 
                " WHERE `" . $this->cacheDbKeyField . "`='" . $this->db->addEscape($key) . "'";
        return time() - (int) $this->db->getValue($sql, $this->cacheDbTimeField);
    }
    
    /**
     * Persists data in the cache, uniquely referenced by a key.
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateInterval $ttl
     * @param null|int|\DateInterval $tts
     * @return bool
     */
    public function set(string $key, $value, $ttl = null, $tts = null): bool
    {
        if ($ttl instanceof DateInterval || $tts instanceof DateInterval) {
            throw new TypeNotSupportedException();
        }
        
        $sql =  'INSERT INTO #__' . $this->cacheDbTable . 
                '(' . 
                    $this->cacheDbKeyField . ', ' . 
                    $this->cacheDbValueField . ', ' . 
                    $this->cacheDbTimeField . 
                ')' . 
                ' VALUES(' . 
                    "'" . $this->db->addEscape($key) . "'," . 
                    "'" . $this->db->addEscape($value) . "'," . 
                    "'" . time() . "'" . 
                ')';
        return $this->db->query($sql);
    }
    
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $sql =  'DELETE FROM #__' . $this->cacheDbTable . 
                " WHERE `" . $this->cacheDbKeyField . "`='" . $this->db->addEscape($key) . "'";
        return $this->db->query($sql);
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * @return bool
     */
    public function clear(): bool
    {
        $sql =  'TRUNCATE #__' . $this->cacheDbTable;
        return $this->db->query($sql);
    }
    
    /**
     * Delete an items from the cache by condition.
     * @param int|\DateInterval $tts
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Ufo\Core\TypeNotSupportedException
     */
    public function deleteOutdated($tts): bool
    {
        if ($tts instanceof DateInterval) {
            throw new TypeNotSupportedException();
        }
        
        $minTime = time() - $tts;
        $sql =  'DELETE FROM #__' . $this->cacheDbTable . 
                " WHERE `" . $this->cacheDbTimeField . "`<'" . $minTime . "'";
        return $this->db->query($sql);
    }
}
