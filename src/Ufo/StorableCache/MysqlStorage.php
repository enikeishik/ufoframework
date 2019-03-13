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
use Ufo\Core\Db;
use Ufo\Core\DebugInterface;

/**
 * Cache storage based on project MySQL database.
 */
class MysqlStorage extends AbstractStorage
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
     * @param \Ufo\Core\Db $db
     * @param \Ufo\Core\DebugInterface $debug = null
     */
    public function __construct(Config $config, Db $db, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        $this->db = $db;
        
        if (isset($this->config->cacheMysqlTable)) {
            $this->table = $this->config->cacheMysqlTable;
        }
        if (isset($this->config->cacheMysqlKeyField)) {
            $this->keyField = $this->config->cacheMysqlKeyField;
        }
        if (isset($this->config->cacheMysqlValueField)) {
            $this->valueField = $this->config->cacheMysqlValueField;
        }
        if (isset($this->config->cacheMysqlTimestampField)) {
            $this->timestampField = $this->config->cacheMysqlTimestampField;
        }
        if (isset($this->config->cacheMysqlLifetimeField)) {
            $this->lifetimeField = $this->config->cacheMysqlLifetimeField;
        }
        if (isset($this->config->cacheMysqlSavetimeField)) {
            $this->savetimeField = $this->config->cacheMysqlSavetimeField;
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
        $sql =  'SELECT COUNT(*) AS Cnt FROM #__' . $this->table
                . " WHERE `" . $this->keyField . "`='" . $this->db->addEscape($key) . "'";
        $cnt = $this->db->getValue($sql, 'Cnt');
        if (null === $cnt) {
            return false;
        }
        return 0 < (int) $cnt;
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
        $sql =  'SELECT '
                . '`' . $this->valueField . '`, `' . $this->timestampField . '`,'
                . '`' . $this->lifetimeField . '`, `' . $this->savetimeField . '`'
                . ' FROM `' . $this->table . '`'
                . ' WHERE `' . $this->keyField . '`=' . "'" . $this->db->addEscape($key) . "'";
        try {
            $row = $this->db->getItem($sql);
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
            $sql =  'INSERT INTO #__' . $this->table
                    . '('
                    . '`' . $this->keyField . '`, '
                    . '`' . $this->valueField . '`, '
                    . '`' . $this->timestampField . '`,'
                    . '`' . $this->lifetimeField . '`,'
                    . '`' . $this->savetimeField . '`'
                    . ')'
                    . ' VALUES('
                    . "'" . $this->db->addEscape($key) . "',"
                    . "'" . $this->db->addEscape($value) . "',"
                    . "'" . time() . "',"
                    . "'" . $lifetime . "',"
                    . "'" . $savetime . "'"
                    . ')';
        } else {
            $sql =  'UPDATE #__' . $this->table
                    . ' SET '
                    . '`' . $this->valueField . '`=' . "'" . $this->db->addEscape($value) . "',"
                    . '`' . $this->timestampField . '`=' . "'" . time() . "',"
                    . '`' . $this->lifetimeField . '`=' . "'" . $lifetime . "',"
                    . '`' . $this->savetimeField . '`=' . "'" . $savetime . "'"
                    . ' WHERE `' . $this->keyField . '`='
                    . "'" . $this->db->addEscape($key) . "'";
        }
        return $this->db->query($sql);
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
        $sql =  'DELETE FROM #__' . $this->table
                . " WHERE `" . $this->keyField
                . "`='" . $this->db->addEscape($key) . "'";
        return $this->db->query($sql);
    }
    
    /**
     * Deletes all outdated (time to save expired) cache items in a single operation.
     * 
     * @return bool True on success and false on failure.
     */
    public function deleteOutdated(): bool
    {
        $sql =  'DELETE FROM `' . $this->table . '`'
                . ' WHERE `' . $this->savetimeField . '`<'
                . '(' . time() . '-`' . $this->timestampField . '`)';
        return $this->db->query($sql);
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * 
     * @return bool
     */
    public function clear(): bool
    {
        $sql =  'TRUNCATE #__' . $this->table;
        return $this->db->query($sql);
    }
}
