<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\StorableCache;

use Ufo\Core\Config;
use Ufo\Core\DebugInterface;

/**
 * Cache storage based on standalone SQLite database.
 */
class SqliteStorage extends AbstractStorage
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
    protected $timestampField = 'created';
    
    /**
     * @var string
     */
    protected $lifetimeField = 'lifetime';
    
    /**
     * @var string
     */
    protected $savetimeField = 'savetime';
    
    /**
     * @param \Ufo\Core\Config $config
     * @param \Ufo\Core\DebugInterface $debug = null
     */
    public function __construct(Config $config, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        
        if (isset($this->config->cacheSqliteTable)) {
            $this->table = $this->config->cacheSqliteTable;
        }
        if (isset($this->config->cacheSqliteKeyField)) {
            $this->keyField = $this->config->cacheSqliteKeyField;
        }
        if (isset($this->config->cacheSqliteValueField)) {
            $this->valueField = $this->config->cacheSqliteValueField;
        }
        if (isset($this->config->cacheSqliteTimestampField)) {
            $this->timestampField = $this->config->cacheSqliteTimestampField;
        }
        if (isset($this->config->cacheSqliteLifetimeField)) {
            $this->lifetimeField = $this->config->cacheSqliteLifetimeField;
        }
        if (isset($this->config->cacheSqliteSavetimeField)) {
            $this->savetimeField = $this->config->cacheSqliteSavetimeField;
        }
        
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
     * 
     * @param string $key
     * 
     * @return bool
     */
    public function has(string $key): bool
    {
        $sql =  'SELECT COUNT(*) AS Cnt FROM "' . $this->table . '"' . 
                ' WHERE "' . $this->keyField . '"=' . "'" . $this->db->escapeString($key) . "'";
        $cnt = null;
        try {
            $cnt = $this->db->querySingle($sql);
            return 0 < (int) $cnt;
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * Fetches an item (packet) from the cache.
     * 
     * @param string $key
     * 
     * @return \Ufo\StorableCache\Packet
     * 
     * @throws \Ufo\StorableCache\BadPacketException
     */
    public function getPacket(string $key): Packet
    {
        //used ` instead of " to gets error if field wrong, not "wrong_field" string
        $sql =  'SELECT '
                . '`' . $this->valueField . '`, `' . $this->timestampField . '`,'
                . '`' . $this->lifetimeField . '`, `' . $this->savetimeField . '`'
                . ' FROM "' . $this->table . '"'
                . ' WHERE "' . $this->keyField . '"=' . "'" . $this->db->escapeString($key) . "'";
        try {
            $row = $this->db->querySingle($sql, true);
            return new Packet(
                $row[$this->valueField], 
                $row[$this->lifetimeField], 
                $row[$this->savetimeField], 
                $row[$this->timestampField]
            );
        } catch (\Throwable $e) {
            throw new BadPacketException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Persists data in the cache, uniquely referenced by a key with an optional lifetime and time to save.
     * 
     * @param string $key   The key of the item to store.
     * @param string $value The value of the item to store.
     * @param int $lifetime The lifetime value of this item.
     *                      The library must store items with expired lifetime.
     * @param int $savetime The time to save value of this item. 
     *                      The library can delete outdated items automatically with expired savetime.
     * 
     * @return bool True on success and false on failure.
     */
    public function set(string $key, string $value, int $lifetime, int $savetime): bool
    {
        if (!$this->has($key)) {
            $sql =  'INSERT INTO "' . $this->table . '"'
                    . '('
                    . '"' . $this->keyField .  '", '
                    . '"' . $this->valueField . '", '
                    . '"' . $this->timestampField . '",'
                    . '"' . $this->lifetimeField . '",'
                    . '"' . $this->savetimeField . '"'
                    . ')'
                    . ' VALUES('
                    . "'" . $this->db->escapeString($key) . "',"
                    . "'" . $this->db->escapeString($value) . "',"
                    . "'" . time() . "',"
                    . "'" . $this->db->escapeString($lifetime) . "',"
                    . "'" . $this->db->escapeString($savetime) . "'"
                    . ')';
        } else {
            $sql =  'UPDATE "' . $this->table . '"'
                    . ' SET '
                    . '"' . $this->valueField . '"=' . "'" . $this->db->escapeString($value) . "',"
                    . '"' . $this->timestampField . '"=' . "'" . time() . "',"
                    . '"' . $this->lifetimeField . '"=' . "'" . $lifetime . "',"
                    . '"' . $this->savetimeField . '"=' . "'" . $savetime . "'"
                    . ' WHERE "' . $this->keyField . '"='
                    . "'" . $this->db->escapeString($key) . "'";
        }
        try {
            return $this->db->exec($sql);
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * Delete an item from the cache by its unique key.
     * 
     * @param string $key The unique cache key of the item to delete.
     * 
     * @return bool True if the item was successfully removed. False if there was an error.
     */
    public function delete(string $key): bool
    {
        $sql =  'DELETE FROM "' . $this->table . '"'
                . ' WHERE "' . $this->keyField . '"=' . "'" . $this->db->escapeString($key) . "'";
        try {
            return $this->db->exec($sql);
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * Deletes all outdated (time to save expired) cache items in a single operation.
     * 
     * @return bool True on success and false on failure.
     */
    public function deleteOutdated(): bool
    {
        $sql =  'DELETE FROM "' . $this->table . '"'
                . ' WHERE "' . $this->savetimeField . '"<'
                . "(" . time() . '-"' . $this->timestampField . '")';
        try {
            return $this->db->exec($sql);
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * 
     * @return bool
     */
    public function clear(): bool
    {
        $sql =  'DELETE FROM "' . $this->table . '"';
        //$sql2 =  'VACUUM'; too slow
        try {
            return $this->db->exec($sql);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
