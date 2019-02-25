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
 * Cache storage based on Memcached service.
 * Memcache extension not available on travis-ci and requires manually start mamcached service on local.
 * @codeCoverageIgnore
 */
class CacheMemcachedStorage implements CacheStorageInterface
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
     * @throws \Ufo\Cache\CacheStorageNotSupportedException
     * @throws \Ufo\Cache\CacheStorageConnectException
     */
    public function __construct(Config $config, DebugInterface $debug = null)
    {
        if (!class_exists('\Memcache')) {
            throw new CacheStorageNotSupportedException();
        }
        
        $this->config = $config;
        $this->debug = $debug;
        if (isset($this->config->cacheMemcachedHost)) {
            $this->host =  $this->config->cacheMemcachedHost;
        }
        if (isset($this->config->cacheMemcachedPort)) {
            $this->port =  $this->config->cacheMemcachedPort;
        }
        
        $this->db = new \Memcache();
        try {
            if (false === $this->db->connect($this->host, $this->port)) {
                throw new CacheStorageConnectException();
            }
        } catch (\Throwable $e) {
            throw new CacheStorageConnectException();
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
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return false !== $this->db->get($key);
    }
    
    /**
     * Fetches a value from the cache.
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $packet = $this->db->get($key);
        if (false === $packet || !($packet instanceof CacheMemcachedPacket)) {
            return null;
        }
        return $this->getPacketValue($packet);
    }
    
    /**
     * Fetches a value age from the cache.
     * @param string $key
     * @return int
     */
    public function getAge(string $key): int
    {
        return time() - $this->getPacketTimestamp($this->db->get($key));
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
        
        return $this->db->set($key, $this->getPacket($value), 0, $tts ?? 0);
    }
    
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->db->delete($key);
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * @return bool
     */
    public function clear(): bool
    {
        return $this->db->flush();
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
        return true;
    }
    
    /**
     * @param mixed $value
     * @return \Ufo\Cache\CacheMemcachedPacket
     */
    protected function getPacket($value): CacheMemcachedPacket
    {
        return new CacheMemcachedPacket($value);
    }
    
    /**
     * @param \Ufo\Cache\CacheMemcachedPacket $packet
     * @return mixed
     */
    protected function getPacketValue(CacheMemcachedPacket $packet)
    {
        return $packet->getValue();
    }
    
    /**
     * @param \Ufo\Cache\CacheMemcachedPacket $packet
     * @return int
     */
    protected function getPacketTimestamp(CacheMemcachedPacket $packet): int
    {
        return $packet->getTimestamp();
    }
}
