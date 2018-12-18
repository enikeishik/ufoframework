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

/**
 * Cache filesystem based storage.
 */
class CacheFsStorage implements CacheStorageInterface
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
     * @var string
     */
    protected $cacheDir = '';
    
    /**
     * @param \Ufo\Core\Config $config
     * @param \Ufo\Core\DebugInterface $debug = null
     */
    public function __construct(Config $config, DebugInterface $debug = null)
    {
        $this->config = $config;
        $this->debug = $debug;
        $this->cacheDir =  $this->config->rootPath . $this->config->cacheDir;
    }
    
    /**
     * Fetches a value from the cache.
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $cacheFile = $this->getCashFilePath($key);
        if (!is_readable($cacheFile)) {
            return false;
        }
        return file_get_contents($cacheFile);
    }
    
    /**
     * Fetches a values age from the cache.
     * @param string $key
     * @return int
     */
    public function getAge(string $key)
    {
        //TODO: check the need to call
        clearstatcache();
        //TODO: check on *nix systems filemtime | filectime
        return time() - filemtime($this->getCashFilePath($key));
    }
    
    /**
     * Persists data in the cache, uniquely referenced by a key.
     * @param string
     * @param mixed
     * @return bool
     */
    public function set(string $key, $value): bool
    {
        $cacheFile = $this->getCashFilePath($key);
        
        if (file_exists($cacheFile)) {
            if (md5($value) == md5_file($cacheFile)) {
                if (touch($cacheFile)) {
                    return true;
                }
            }
        }
        
        if (!$handle = fopen($cacheFile, 'w')) {
            return false;
        }
        
        $written = false;
        
        if (flock($handle, LOCK_EX | LOCK_NB)) {
            fwrite($handle, $value);
            fflush($handle);
            flock($handle, LOCK_UN);
            $written = true;
        }
        
        fclose($handle);
        
        return $written;
    }
    
    /**
     * Delete an item from the cache by its unique key.
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return unlink($this->getCashFilePath($key));
    }
    
    /**
     * Wipes clean the entire cache's keys.
     * @return bool
     */
    public function clear(): bool
    {
        $dh = opendir($this->cacheDir);
        if (false === $dh) {
            return false;
        }
        
        while (false !== ($entry = readdir($dh))) {
            $file = $this->cacheDir . '/' . $entry;
            if (is_file($file) && 0 !== strpos($entry, '.')) { //exclude .htaccess
                unlink($file);
            }
        }
        
        closedir($dh);
        
        return true;
    }
    
    /**
     * Determines whether an item is present in the cache.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return file_exists($this->getCashFilePath($key));
    }
    
    /**
     * @param string $key
     * @return string
     */
    protected function getCashFilePath(string $key): string
    {
        return $this->cacheDir . '/' . $this->getHash($key);
    }
    
    /**
     * @param string $key
     * @return string
     */
    protected function getHash(string $key): string
    {
        if ('' == $key) {
            return 'empty,' . time();
        } elseif (preg_match('/[^A-Za-z0-9~_,\.\/\-]|(\.{2})/', $key)) {
            return md5($hash);
        }
        return str_replace('/', ',', $key);
    }
}
