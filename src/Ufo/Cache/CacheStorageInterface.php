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
    public function getAge(string $key);
    
    /**
     * Persists data in the cache, uniquely referenced by a key.
     * @param string
     * @param mixed
     * @return bool
     */
    public function set(string $key, $value): bool;
    
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;
    
    /**
     * Wipes clean the entire cache's keys.
     * @return bool
     */
    public function clear(): bool;
    
    /**
     * Determines whether an item is present in the cache.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;
}
