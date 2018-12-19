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
 * Cache storage interface.
 */
interface CacheStorageInterface
{
    /**
     * Determines whether an item is present in the cache.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;
    
    /**
     * Fetches a value from the cache.
     * @param string $key
     * @return mixed
     */
    public function get(string $key);
    
    /**
     * Fetches a values age from the cache.
     * @param string $key
     * @return int
     */
    public function getAge(string $key): int;
    
    /**
     * Persists data in the cache, uniquely referenced by a key.
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateInterval $ttl
     * @param null|int|\DateInterval $tts
     * @return bool
     */
    public function set(string $key, $value, $ttl = null, $tts = null): bool;
    
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;
    
    /**
     * Delete an outdated items from the cache.
     * @param int|\DateInterval $tts
     * @return bool
     */
    public function deleteOutdated($tts): bool;
    
    /**
     * Wipes clean the entire cache's keys.
     * @return bool
     */
    public function clear(): bool;
}
