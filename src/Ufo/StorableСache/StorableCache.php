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
 * StorableCache class.
 * 
 * Storable cache store items more than their lifetime, up to time to save, 
 * and allow use stored items after expiration, in cases, for example, of db overload or errors.
 */
class StorableCache implements StorableCacheInterface
{
    /**
     * @var int
     */
    protected const LIFETIME = 3;
    
    /**
     * @var int
     */
    protected const SAVETIME = 3600;
    
    /**
     * @var \Ufo\Core\Config
     */
    protected $config = null;
    
    /**
     * @var \Ufo\Core\DebugInterface
     */
    protected $debug = null;
    
    /**
     * @var \Ufo\StorableCache\StorageInterface
     */
    protected $storage = null;
    
    /**
     * @param \Ufo\Core\Config $config
     * @param \Ufo\Core\DebugInterface $debug = null
     * @throws \Ufo\StorableCache\StorageNotSupportedException
     * @throws \Ufo\StorableCache\StorageConnectException
     */
    public function __construct(Config $config, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        
        switch ($this->config->cacheType) {
            
            case $this->config::CACHE_TYPE_FILES:
                $this->storage = new FilesStorage($this->config, $this->debug);
                break;
            
            case $this->config::CACHE_TYPE_SQLITE:
                $this->storage = new SqliteStorage($this->config, $this->debug);
                break;
            
            // @codeCoverageIgnoreStart
            case $this->config::CACHE_TYPE_MEMCACHED:
                $this->storage = new MemcachedStorage($this->config, $this->debug);
                break;
            // @codeCoverageIgnoreEnd
            
            case $this->config::CACHE_TYPE_REDIS:
                $this->storage = new RedisStorage($this->config, $this->debug);
                break;
            
            case $this->config::CACHE_TYPE_MYSQL:
                $this->storage = new MysqlStorage(
                    $this->config, 
                    new Db($this->config, $this->debug), 
                    $this->debug
                );
                break;
            
            case $this->config::CACHE_TYPE_ARRAY:
                $this->storage = new ArrayStorage();
                break;
            
            default:
                throw new StorageNotSupportedException();
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
        return $this->storage->has($key);
    }
    
    /**
     * Fetches a value from the cache.
     * 
     * @param string $key       The unique key of this item in the cache.
     * @param string $default   Default value to return if the key does not exist.
     * 
     * @return string The value of the item from the cache, or $default in case of cache miss.
     */
    public function get(string $key, string $default = ''): string
    {
        return $this->storage->get($key) ?: $default;
    }
    
    /**
     * Persists data in the cache, uniquely referenced by a key with an optional lifetime and time to save.
     * 
     * @param string $key   The key of the item to store.
     * @param string $value The value of the item to store.
     * @param int $lifetime Optional. The lifetime value of this item. If no value is sent 
     *                      then the library must set a default value for it.
     * @param int $savetime Optional. The time to save value of this item. If no value is sent 
     *                      then the library must set a default value for it.
     * 
     * @return bool True on success and false on failure.
     */
    public function set(string $key, string $value, int $lifetime = 0, int $savetime = 0): bool
    {
        return $this->storage->set($key, $value, $lifetime, $savetime);
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
        return $this->storage->delete($key);
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * 
     * @return bool True on success and false on failure.
     */
    public function clear(): bool
    {
        return $this->storage->clear();
    }
    
    /**
     * Determines whether an item is present in the cache and life time expired.
     * 
     * @param string $key The unique cache key of the item to check for expiring.
     * 
     * @return bool True if the item was expired. False if not.
     */
    public function expired(string $key): bool
    {
        
    }
    
    /**
     * Deletes all outdated (time to save expired) cache items in a single operation.
     * 
     * @return bool True on success and false on failure.
     */
    public function deleteOutdated(): bool
    {
        
    }
}
