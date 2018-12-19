<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Cache;

/**
 * Cache interface.
 */
interface CacheInterface //extends \Psr\SimpleCache\CacheInterface
{
    /**
     * Determines whether an item is present in the cache.
     * @param string $key
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function has(string $key): bool;
    
    /**
     * Fetches a value from the cache.
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string $key, $default = null);
    
    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateInterval $ttl
     * @param null|int|\DateInterval $tts
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set(string $key, $value, $ttl = null, $tts = null): bool;
    
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete(string $key): bool;
    
    /**
     * Wipes clean the entire cache's keys.
     * @return bool
     */
    public function clear(): bool;
    
    /**
     * Obtains multiple cache items by their unique keys.
     * @param iterable $keys
     * @param mixed $default
     * @return iterable
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple(iterable $keys, $default = null): iterable;
    
    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     * @param iterable $values
     * @param null|int|\DateInterval $ttl
     * @param null|int|\DateInterval $tts
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple(iterable $values, $ttl = null, $tts = null): bool;
    
    /**
     * Deletes multiple cache items in a single operation.
     * @param iterable $keys
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple(iterable $keys): bool;
    
    /**
     * Determines whether an item is present in the cache and expired.
     * @param string $key
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function expired(string $key): bool;
    
    /**
     * Deletes all outdated cache items in a single operation.
     * @param int|\DateInterval $storageTime
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteOutdated($storageTime): bool;
}
