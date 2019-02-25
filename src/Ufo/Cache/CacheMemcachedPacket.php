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
 * Cache storage based on Memcached service.
 * Memcache extension not available on travis-ci and requires manually start mamcached service on local.
 * @codeCoverageIgnore
 */
class CacheMemcachedPacket
{
    /**
     * @var mixed
     */
    protected $value;
    
    /**
     * @var int
     */
    protected $timestamp;
    
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
        $this->timestamp = time();
    }
    
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
