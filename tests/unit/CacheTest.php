<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Config;
use \Ufo\Core\Debug;
use \Ufo\Cache\Cache;
 
class CacheTest extends \Codeception\Test\Unit
{
    /**
     * @param string $expectedExceptionClass
     * @param callable $call = null
     */
    protected function expectedException(string $expectedExceptionClass, callable $call = null)
    {
        try {
            $call();
        } catch (\Exception $e) {
            $this->assertEquals($expectedExceptionClass, get_class($e));
        }
    }
    
    // tests
    public function testCacheArrayStorage()
    {
        $config = new Config();
        $config->cacheType = 'array';
        $cache = new Cache($config, new Debug());
        
        $this->assertEmpty($cache->get('any-key'));
        $this->assertTrue($cache->set('any-key', 'any-value'));
        $this->assertTrue($cache->delete('any-key'));
        $this->assertTrue($cache->clear());
        $this->assertFalse($cache->has('any-key'));
    }
    
    public function testCacheFsStorage()
    {
        $cacheDir = 'c:/tmp/ufo-cache-test-dir';
        mkdir($cacheDir);
        
        $config = new Config();
        $config->cacheType = 'fs';
        $config->rootPath = '';
        $config->cacheDir = $cacheDir;
        $cache = new Cache($config, new Debug());
        
        $this->assertEmpty($cache->get('any-key'));
        $this->assertTrue($cache->set('any-key', 'any-value'));
        $this->assertTrue($cache->has('any-key'));
        $this->assertEquals('any-value', $cache->get('any-key'));
        
        $this->assertTrue($cache->delete('any-key'));
        $this->assertEmpty($cache->get('any-key'));
        
        $cache->set('any-key', 'any-value');
        $this->assertTrue($cache->clear());
        $this->assertEmpty($cache->get('any-key'));
        
        rmdir($cacheDir);
    }
}
