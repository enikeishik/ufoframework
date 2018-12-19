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
        $this->assertEquals('default-value', $cache->get('any-key', 'default-value'));
        $this->assertTrue($cache->set('any-key', 'any-value'));
        $this->assertTrue($cache->delete('any-key'));
        $this->assertTrue($cache->clear());
        $this->assertFalse($cache->has('any-key'));
        
        $this->assertTrue($cache->setMultiple([
            'key1' => 'value1', 
            'key2' => 'value2', 
            'key3' => 'value3', 
            'key4' => 'value4', 
        ]));
        $this->assertFalse($cache->has('key1'));
        $this->assertEquals([null, null], $cache->getMultiple(['key1', 'key2']));
        $this->assertEquals(
            ['default-value', 'default-value'], 
            $cache->getMultiple(['key1', 'key2'], 'default-value')
        );
        $this->assertTrue($cache->deleteMultiple(['key1', 'key2', 'key3', 'key4']));
        
        $this->assertTrue($cache->expired('key1', 0));
        $this->assertTrue($cache->expired('key1', PHP_INT_MAX));
        
        $this->assertTrue($cache->deleteOutdated(0));
        
        $this->assertTrue($cache->clear());
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
        $this->assertEquals('default-value', $cache->get('any-key', 'default-value'));
        
        $this->assertTrue($cache->set('any-key', 'any-value'));
        $this->assertTrue($cache->has('any-key'));
        $this->assertEquals('any-value', $cache->get('any-key'));
        $this->assertEquals('any-value', $cache->get('any-key', 'default-value'));
        
        $this->assertTrue($cache->delete('any-key'));
        $this->assertEmpty($cache->get('any-key'));
        
        $cache->set('any-key', 'any-value');
        $this->assertTrue($cache->clear());
        $this->assertEmpty($cache->get('any-key'));

        $this->assertTrue($cache->setMultiple([
            'key1' => 'value1', 
            'key2' => 'value2', 
            'key3' => 'value3', 
            'key4' => 'value4', 
        ]));
        $this->assertTrue($cache->has('key1'));
        $this->assertEquals(
            ['value3', 'value1', null], 
            $cache->getMultiple(['key3', 'key1', 'key0'])
        );
        $this->assertEquals(
            ['value2', 'default-value', 'default-value'], 
            $cache->getMultiple(['key2', 'key-4', 'key-1'], 'default-value')
        );
        $this->assertTrue($cache->deleteMultiple(['key1', 'key2', 'key3', 'key4']));
        $this->assertFalse($cache->has('key2'));
        
        $this->assertTrue($cache->expired('key-not-exists', 0));
        $this->assertTrue($cache->expired('key-not-exists', PHP_INT_MAX));
        $cache->set('key1', 'value1');
        $this->assertFalse($cache->expired('key1', PHP_INT_MAX));
        sleep(1);
        $this->assertTrue($cache->expired('key1', 0));
        $this->assertFalse($cache->expired('key1', 2));

        $this->assertTrue($cache->deleteOutdated(100));
        $this->assertTrue($cache->has('key1'));
        $this->assertTrue($cache->deleteOutdated(0));
        $this->assertFalse($cache->has('key1'));
        
        $cache->clear();
        rmdir($cacheDir);
    }
}
