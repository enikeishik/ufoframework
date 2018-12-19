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
use Ufo\Core\DebugInterface;
use Ufo\Core\TypeNotSupportedException;

/**
 * Cache class.
 */
class Cache implements CacheInterface
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
     * @var \Ufo\Cache\CacheStorageInterface
     */
    protected $storage = null;
    
    /**
     * @param \Ufo\Core\Config $config
     * @param \Ufo\Core\DebugInterface $debug = null
     * @throws CacheStorageNotSupportedException
     */
    public function __construct(Config $config, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        
        switch ($this->config->cacheType) {
            
            case $this->config::CACHE_TYPE_FS:
                $this->storage = new CacheFsStorage($this->config);
                break;
            
            // case $this->config::CACHE_TYPE_DB:
                // break;
            
            // case $this->config::CACHE_TYPE_REDIS:
                // break;
            
            case $this->config::CACHE_TYPE_ARRAY:
                $this->storage = new CacheArrayStorage();
                break;
            
            default:
                throw new CacheStorageNotSupportedException();
        }
    }
    
    /**
     * Fetches a value from the cache.
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string $key, $default = null)
    {
        return $this->storage->get($key) ?: $default;
    }
    
    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     * @param string
     * @param mixed
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Ufo\Core\TypeNotSupportedException
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        if ($ttl instanceof DateInterval) {
            throw new TypeNotSupportedException();
        }
        
        return $this->storage->set($key, $value);
    }
    
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        return $this->storage->delete($key);
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * @return bool
     */
    public function clear(): bool
    {
        return $this->storage->clear();
    }
    
    /**
     * Obtains multiple cache items by their unique keys.
     * @param iterable $keys
     * @param mixed $default
     * @return iterable
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple(iterable $keys, $default = null): iterable
    {
        return [];
    }
    
    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     * @param iterable $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Ufo\Core\TypeNotSupportedException
     */
    public function setMultiple(iterable $values, $ttl = null): bool
    {
        if ($ttl instanceof DateInterval) {
            throw new TypeNotSupportedException();
        }
        
        return true;
    }
    
    /**
     * Deletes multiple cache items in a single operation.
     * @param iterable $keys
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return true;
    }
    
    /**
     * Determines whether an item is present in the cache.
     * @param string $key
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return $this->storage->has($key);
    }
    
    /**
     * Determines whether an item is present in the cache and expired.
     * @param string $key
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Ufo\Core\TypeNotSupportedException
     */
    public function expired(string $key, $ttl = null): bool
    {
        if ($ttl instanceof DateInterval) {
            throw new TypeNotSupportedException();
        }
        
        if (null === $ttl) {
            return true;
        }
        
        if (!$this->has($key)) {
            return true;
        }
        
        return $ttl < $this->storage->getAge();
    }
    
    /**
     * Deletes all outdated cache items in a single operation.
     * @param int|\DateInterval $storageTime
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteOutdated($storageTime): bool
    {
        return $this->storage->deleteOutdated($storageTime);
    }
}
