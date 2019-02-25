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
 * Cache storage based on Redis service.
 */
class CacheRedisStorage implements CacheStorageInterface
{
    /**
     * @var int
     */
    protected const DEFAULT_TTS = 86400;
    
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
    protected $port = 6379;
    
    /**
     * @param \Ufo\Core\Config $config
     * @param \Ufo\Core\DebugInterface $debug = null
     * @throws \Ufo\Cache\CacheStorageNotSupportedException
     * @throws \Ufo\Cache\CacheStorageConnectException
     */
    public function __construct(Config $config, DebugInterface $debug = null)
    {
        if (!class_exists('\Redis')) {
            throw new CacheStorageNotSupportedException(); // @codeCoverageIgnore
        }
        
        $this->config = $config;
        $this->debug = $debug;
        if (isset($this->config->cacheRedisHost)) {
            $this->host = $this->config->cacheRedisHost;
        }
        if (isset($this->config->cacheRedisPort)) {
            $this->port = $this->config->cacheRedisPort;
        }
        
        $this->db = new \Redis();
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
        return 0 !== $this->db->exists($key);
    }
    
    /**
     * Fetches a value from the cache.
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $packet = unserialize($this->db->get($key));
        if (false === $packet || !($packet instanceof CachePacket)) {
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
        return time() - $this->getPacketTimestamp(unserialize($this->db->get($key)));
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
        
        return $this->db->set(
            $key, 
            serialize($this->getPacket($value)), 
            $tts ?? static::DEFAULT_TTS
        );
    }
    
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return 0 !== $this->db->del($key);
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * @return bool
     */
    public function clear(): bool
    {
        return $this->db->flushall();
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
        if ($tts instanceof \DateInterval) {
            throw new TypeNotSupportedException();
        }
        
        $keys = $this->db->keys('*');
        foreach ($keys as $key) {
            if ($tts < (time() - $this->getPacketTimestamp(unserialize($this->db->get($key))))) {
                $this->delete($key);
            }
        }
        return true;
    }
    
    /**
     * @param mixed $value
     * @return \Ufo\Cache\CachePacket
     */
    protected function getPacket($value): CachePacket
    {
        return new CachePacket($value);
    }
    
    /**
     * @param \Ufo\Cache\CachePacket $packet
     * @return mixed
     */
    protected function getPacketValue(CachePacket $packet)
    {
        return $packet->getValue();
    }
    
    /**
     * @param \Ufo\Cache\CachePacket $packet
     * @return int
     */
    protected function getPacketTimestamp(CachePacket $packet): int
    {
        return $packet->getTimestamp();
    }
}
