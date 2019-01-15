<?php
use \Ufo\Core\Config;
use \Ufo\Core\Db;
use \Ufo\Core\Debug;
use \Ufo\Cache\Cache;
 
class CacheTest extends BaseUnitTest
{
    // tests
    protected function testCacheCases($cache)
    {
        $cache->clear();
        
        $this->assertEmpty($cache->get('any-key'));
        $this->assertEquals('default-value', $cache->get('any-key', 'default-value'));
        
        $this->assertTrue($cache->set('any-key', 'any-value'));
        $this->assertTrue($cache->set('any-key', 'any-value')); //add the same
        $this->assertTrue($cache->set('any-key2', 'any-value2', 2));
        $this->assertTrue($cache->set('any-key3', 'any-value3', 3, 30));
        
        $this->expectedException(
            \Ufo\Core\TypeNotSupportedException::class, 
            function() use($cache) { $cache->set('any-key4', 'any-value4', new DateInterval('PT0S')); }
        );
        
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
        
        $this->expectedException(
            \Ufo\Core\TypeNotSupportedException::class, 
            function() use($cache) { $cache->expired('key1', new DateInterval('PT0S')); }
        );
        
        $this->assertTrue($cache->deleteOutdated(100));
        $this->assertTrue($cache->has('key1'));
        $this->assertTrue($cache->deleteOutdated(0));
        $this->assertFalse($cache->has('key1'));
        
        $this->expectedException(
            \Ufo\Core\TypeNotSupportedException::class, 
            function() use($cache) { $cache->deleteOutdated(new DateInterval('PT0S')); }
        );
        
        $cache->clear();
    }
    
    protected function testCacheCasesFail($cache)
    {
        $this->assertEmpty($cache->get('any-key'));
        $this->assertEquals('default-value', $cache->get('any-key', 'default-value'));
        
        $this->assertFalse($cache->set('any-key', 'any-value'));
        $this->assertFalse($cache->has('any-key'));
        $this->assertNull($cache->get('any-key'));
        $this->assertEquals('default-value', $cache->get('any-key', 'default-value'));
        
        $this->assertFalse($cache->delete('any-key'));
        $this->assertFalse($cache->clear());
        
        $this->assertFalse($cache->setMultiple([
            'key1' => 'value1', 
            'key2' => 'value2', 
            'key3' => 'value3', 
            'key4' => 'value4', 
        ]));
        $this->assertEquals(
            [null, null, null], 
            $cache->getMultiple(['key3', 'key1', 'key0'])
        );
        $this->assertEquals(
            ['default-value', 'default-value', 'default-value'], 
            $cache->getMultiple(['key2', 'key-4', 'key-1'], 'default-value')
        );
        $this->assertFalse($cache->deleteMultiple(['key1', 'key2', 'key3', 'key4']));
        
        $this->assertTrue($cache->expired('key-not-exists', 0));
        $this->assertTrue($cache->expired('key-not-exists', PHP_INT_MAX));

        $this->assertFalse($cache->deleteOutdated(100));
        $this->assertFalse($cache->deleteOutdated(0));
    }
    
    public function testCacheArrayStorage()
    {
        $config = new Config();
        $config->cacheType = Config::CACHE_TYPE_ARRAY;
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
        
        $cache = new class($config, new Debug()) extends Cache {
            public $storage;
        };
        $this->assertEquals(PHP_INT_MAX, $cache->storage->getAge(''));
    }
    
    public function testCacheFilesStorage()
    {
        if (strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0) {
            $cacheDir = 'c:/tmp/ufo-cache-test-dir';
        } else {
            $cacheDir = '/tmp/ufo-cache-test-dir';
        }
        if (file_exists($cacheDir)) {
            rmdir($cacheDir);
        }
        mkdir($cacheDir);
        
        $config = new Config();
        $config->cacheType = Config::CACHE_TYPE_FILES;
        $config->rootPath = '';
        $config->cacheDir = $cacheDir;
        $cache = new Cache($config, new Debug());
        
        $this->testCacheCases($cache);
        
        $this->assertFalse($cache->has(''));
        $this->assertFalse($cache->has('asd@123.qwe!zxc#rty$456'));
        
        rmdir($cacheDir);
        
        $config->cacheDir = '/tmp/unexistence-ufo-cache-test-dir';
        $cache = new Cache($config, new Debug());
        $this->testCacheCasesFail($cache);
    }
    
    public function testCacheSqliteStorage()
    {
        $config = new Config();
        $config->cacheType = Config::CACHE_TYPE_SQLITE;
        $config->rootPath = '';
        $config->cacheDir = dirname(__DIR__) . '/_data';
        $cache = new Cache($config, new Debug());
        $this->testCacheCases($cache);
        
        //test storage::getAge with wrong query
        $this->assertTrue($cache->set('key-expired-test', 'value expired test'));
        $config->cacheSqliteTimeField = 'non_exists_time_field';
        $cache = new Cache($config, new Debug());
        $this->assertTrue($cache->expired('key-expired-test', 0));
        
        $config->cacheSqliteTable = 'non_exists_time_field';
        $config->cacheSqliteKeyField = 'non_exists_time_field';
        $config->cacheSqliteValueField = 'non_exists_time_field';
        $cache = new Cache($config, new Debug());
        $this->testCacheCasesFail($cache);
    }
    
    public function testCacheMysqlStorage()
    {
        $config = new Config();
        $config->loadFromIni(dirname(__DIR__) . '/_data/.config', true);
        $config->cacheType = Config::CACHE_TYPE_MYSQL;
        $cache = new Cache($config, new Debug());
        $this->testCacheCases($cache);
        
        //test storage::getAge with wrong query
        $this->assertTrue($cache->set('key-expired-test', 'value expired test'));
        Db::getInstance($config)->close();
        $config->cacheMysqlTimeField = 'non_exists_time_field';
        $cache = new Cache($config, new Debug());
        $this->assertTrue($cache->expired('key-expired-test', 0));
        Db::getInstance($config)->close();
        
        $config->cacheMysqlTable = 'non_exists_time_field';
        $config->cacheMysqlKeyField = 'non_exists_time_field';
        $config->cacheMysqlValueField = 'non_exists_time_field';
        $cache = new Cache($config, new Debug());
        $this->testCacheCasesFail($cache);
    }
    
    public function testCacheUnsupportedStorage()
    {
        $config = new Config();
        $config->cacheType = '';
        $this->expectedException(
            \Ufo\Cache\CacheStorageNotSupportedException::class, 
            function() use($config) { $cache = new Cache($config, new Debug()); }
        );
    }
}
