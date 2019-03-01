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
 * Cache storage based on Memcached service.
 * Memcache extension not available on travis-ci and requires manually start mamcached service on local.
 * @codeCoverageIgnore
 */
class MemcachedStorage implements StorageInterface
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
     * @var \Memcache
     */
    protected $db = null;
    
    /**
     * @var string
     */
    protected $host = 'localhost';
    
    /**
     * @var int
     */
    protected $port = 11211;
    
    /**
     * @param \Ufo\Core\Config $config
     * @param \Ufo\Core\DebugInterface $debug = null
     * @throws \Ufo\StorableCache\StorageNotSupportedException
     * @throws \Ufo\StorableCache\StorageConnectException
     */
    public function __construct(Config $config, DebugInterface $debug = null)
    {
        if (!class_exists('\Memcache')) {
            throw new StorageNotSupportedException();
        }
        
        $this->config = $config;
        $this->debug = $debug;
        if (isset($this->config->cacheMemcachedHost)) {
            $this->host = $this->config->cacheMemcachedHost;
        }
        if (isset($this->config->cacheMemcachedPort)) {
            $this->port = $this->config->cacheMemcachedPort;
        }
        
        $this->db = new \Memcache();
        try {
            if (false === $this->db->connect($this->host, $this->port)) {
                throw new StorageConnectException();
            }
        } catch (\Throwable $e) {
            throw new StorageConnectException();
        }
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
        return false !== $this->db->get($key);
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
        $packet = unserialize($this->db->get($key));
        if (false === $packet || !($packet instanceof Packet)) {
            throw new BadPacketException();
        }
        return $packet;
    }
    
    /**
     * Fetches an item value from the cache.
     * 
     * @param string $key
     * 
     * @return string
     * 
     * @throws \Ufo\StorableCache\BadPacketException
     */
    public function getValue(string $key): string
    {
        return $this->getPacket($key)->getValue();
    }
    
    /**
     * Determines whether an item is present in the cache and life time expired.
     * 
     * @param string $key The unique cache key of the item to check for expiring.
     * 
     * @return bool True if the item was expired. False if not.
     * 
     * @throws \Ufo\StorableCache\BadPacketException
     */
    public function expired(string $key): bool
    {
        return $this->getPacket($key)->expired();
    }
    
    /**
     * Fetches an item value from the cache. Synonym for getValue method.
     * 
     * @param string $key
     * 
     * @return string
     * 
     * @throws \Ufo\StorableCache\BadPacketException
     */
    public function get(string $key): string
    {
        return $this->getValue($key);
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
        return $this->db->set(
            $key, 
            serialize(new Packet($value, $lifetime, $savetime)), 
            0, 
            $savetime
        );
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
        return $this->db->delete($key);
    }
    
    /**
     * Deletes all outdated (time to save expired) cache items in a single operation.
     * 
     * @return bool True on success and false on failure.
     */
    public function deleteOutdated(): bool
    {
        return true;
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * 
     * @return bool
     */
    public function clear(): bool
    {
        return $this->db->flush();
    }
}
