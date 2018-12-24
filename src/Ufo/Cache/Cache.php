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
            
            case $this->config::CACHE_TYPE_FILES:
                $this->storage = new CacheFilesStorage($this->config);
                break;
            
            case $this->config::CACHE_TYPE_SQLITE:
                $this->storage = new CacheSqliteStorage($this->config);
                break;
            
            // case $this->config::CACHE_TYPE_MEMCACHED:
                // break;
            // case $this->config::CACHE_TYPE_REDIS:
                // break;
            // case $this->config::CACHE_TYPE_MYSQL:
                // $this->storage = new CacheMysqlStorage($this->config);
                // break;
            
            case $this->config::CACHE_TYPE_ARRAY:
                $this->storage = new CacheArrayStorage();
                break;
            
            default:
                throw new CacheStorageNotSupportedException();
        }
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
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateInterval $ttl
     * @param null|int|\DateInterval $tts
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set(string $key, $value, $ttl = null, $tts = null): bool
    {
        return $this->storage->set($key, $value, $ttl, $tts);
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
        $values = [];
        foreach ($keys as $key) {
            $values[] = $this->get($key, $default);
        }
        return $values;
    }
    
    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     * @param iterable $items
     * @param null|int|\DateInterval $ttl
     * @param null|int|\DateInterval $tts
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple(iterable $items, $ttl = null, $tts = null): bool
    {
        foreach ($items as $key => $value) {
            if (!$this->set($key, $value, $ttl, $tts)) {
                return false;
            }
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
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Determines whether an item is present in the cache and expired.
     * @param string $key
     * @param int|\DateInterval $ttl
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Ufo\Core\TypeNotSupportedException
     */
    public function expired(string $key, $ttl): bool
    {
        if ($ttl instanceof \DateInterval) {
            throw new TypeNotSupportedException();
        }
        
        if (!$this->has($key)) {
            return true;
        }
        
        return $ttl < $this->storage->getAge($key);
    }
    
    /**
     * Deletes all outdated cache items in a single operation.
     * @param int|\DateInterval $tts
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteOutdated($tts): bool
    {
        return $this->storage->deleteOutdated($tts);
    }
}
