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
use Ufo\Core\TypeNotSupportedException;

/**
 * Cache database based storage.
 */
class CacheSqliteStorage implements CacheStorageInterface
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
     * @var \SQLite3
     */
    protected $db = null;
    
    /**
     * @var string
     */
    protected $base = 'cache.db';
    
    /**
     * @var string
     */
    protected $table = 'cache';
    
    /**
     * @var string
     */
    protected $keyField = 'key';
    
    /**
     * @var string
     */
    protected $valueField = 'value';
    
    /**
     * @var string
     */
    protected $timeField = 'time';
    
    /**
     * @param \Ufo\Core\Config $config
     * @param \Ufo\Core\DebugInterface $debug = null
     */
    public function __construct(Config $config, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        
        $this->db = new \SQLite3($this->config->projectPath . $this->config->cacheDir . '/' . $this->base);
    }
    
    public function __destruct()
    {
        if (null !== $this->db) {
            $this->db->close();
        }
    }
    
    /**
     * Determines whether an item is present in the cache.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $sql =  'SELECT COUNT(*) AS Cnt FROM "' . $this->table . '"' . 
                ' WHERE "' . $this->keyField . '"=' . "'" . $this->db->escapeString($key) . "'";
        $cnt = $this->db->querySingle($sql);
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
        $sql =  'SELECT "' . $this->valueField . '" FROM "' . $this->table . '"' . 
                ' WHERE "' . $this->keyField . '"=' . "'" . $this->db->escapeString($key) . "'";
        return $this->db->querySingle($sql);
    }
    
    /**
     * Fetches a value age from the cache.
     * @param string $key
     * @return int
     */
    public function getAge(string $key): int
    {
        $sql =  'SELECT "' . $this->timeField . '" FROM "' . $this->table . '"' . 
                ' WHERE "' . $this->keyField . '"=' . "'" . $this->db->escapeString($key) . "'";
        return time() - (int) $this->db->querySingle($sql);
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
        if ($ttl instanceof \DateInterval || $tts instanceof \DateInterval) {
            throw new TypeNotSupportedException();
        }
        
        $sql =  'INSERT INTO "' . $this->table . '"' . 
                '(' . 
                    '"' . $this->keyField .   '", ' . 
                    '"' . $this->valueField . '", ' . 
                    '"' . $this->timeField .  '"' . 
                ')' . 
                ' VALUES(' . 
                    "'" . $this->db->escapeString($key) . "'," . 
                    "'" . $this->db->escapeString($value) . "'," . 
                    "'" . time() . "'" . 
                ')';
        return $this->db->exec($sql);
    }
    
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $sql =  'DELETE FROM "' . $this->table . '"' . 
                ' WHERE "' . $this->keyField . '"=' . "'" . $this->db->escapeString($key) . "'";
        return $this->db->exec($sql);
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * @return bool
     */
    public function clear(): bool
    {
        $sql =  'DELETE FROM "' . $this->table . '"';
        //$sql2 =  'VACUUM'; too slow
        return $this->db->exec($sql);
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
        if ($tts instanceof \DateInterval) {
            throw new TypeNotSupportedException();
        }
        
        $minTime = time() - $tts;
        $sql =  'DELETE FROM "' . $this->table . '"' . 
                ' WHERE "' . $this->timeField . '"<' . "'" . $minTime . "'";
        return $this->db->exec($sql);
    }
}
