<?php
require_once dirname(__DIR__) . '/_data/cctestsdb.php';

use \Ufo\Core\Db;
use \Ufo\Core\Debug;

class FrontendDbTest extends BaseUnitTest
{
    protected function getDb($withDebug = true)
    {
        return Db::getInstance($withDebug ? new Debug() : null);
    }

    protected function _after()
    {
        $this->getDb()->close();
    }

    // tests
    public function testGetInstance()
    {
        $db1 = Db::getInstance();
        $db2 = Db::getInstance();
        $db3 = Db::getInstance(new Debug());
        $this->assertSame($db1, $db2);
        $this->assertSame($db1, $db3);
    }
    
    public function testQuery()
    {
        $this->assertNotFalse($this->getDb()->query('SHOW TABLES'));
    }
    
    public function testGetItem()
    {
        $db = $this->getDb();
        $item = $db->getItem('SELECT `id` FROM `test_items_table` WHERE `id`=1');
        $this->assertNotNull($item);
        $this->assertTrue(array_key_exists('id', $item));
        $this->assertEquals(1, $item['id']);
        
        $item = $db->getItem('SELECT `id` FROM `test_items_table` WHERE `id`=0');
        $this->assertNull($item);
    }
    
    public function testGetValue()
    {
        $db = $this->getDb();
        $value = $db->getValue('SELECT `id` FROM `test_items_table` WHERE `id`=1', 'id');
        $this->assertNotNull($value);
        $this->assertEquals(1, $value);
        
        $this->assertNull($db->getValue('SELECT `id` FROM `test_items_table` WHERE `id`=0', 'id'));
        $this->assertNull($db->getValue('SELECT `id` FROM `test_items_table` LIMIT 1', 'id2'));
    }
    
    public function testGetValues()
    {
        $db = $this->getDb();
        $values = $db->getValues('SELECT `id` FROM `test_items_table` WHERE `id`=1', 'id');
        $this->assertNotNull($values);
        $this->assertTrue(is_array($values));
        $this->assertCount(1, $values);
        $this->assertEquals(1, $values[0]);
        
        $values = $db->getValues('SELECT `id` FROM `test_items_table` WHERE `id`=1', 'id', 'id');
        $this->assertNotNull($values);
        $this->assertTrue(is_array($values));
        $this->assertCount(1, $values);
        $this->assertTrue(array_key_exists('id1', $values));
        $this->assertEquals(1, $values['id1']);
        
        $values = $db->getValues('SELECT `id` FROM `test_items_table` WHERE `id`=0', 'id');
        $this->assertNotNull($values);
        $this->assertTrue(is_array($values));
        $this->assertCount(0, $values);
        
        $error = '';
        try {
            $db->getValues('SELECT `id` FROM `test_items_table` LIMIT 1', 'id2');
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $this->assertEquals('Undefined index: id2', $error);
    }
    
    public function testGetItems()
    {
        $db = $this->getDb();
        $items = $db->getItems('SELECT `id` FROM `test_items_table` WHERE `id`=1');
        $this->assertNotNull($items);
        $this->assertTrue(is_array($items));
        $this->assertCount(1, $items);
        $this->assertTrue(is_array($items[0]));
        $this->assertTrue(array_key_exists('id', $items[0]));
        $this->assertEquals(1, $items[0]['id']);
        
        $items = $db->getItems('SELECT `id` FROM `test_items_table` WHERE `id`=1', 'id');
        $this->assertNotNull($items);
        $this->assertTrue(is_array($items));
        $this->assertCount(1, $items);
        $this->assertTrue(array_key_exists('id1', $items));
        $this->assertTrue(is_array($items['id1']));
        $this->assertTrue(array_key_exists('id', $items['id1']));
        $this->assertEquals(1, $items['id1']['id']);
        
        $items = $db->getItems('SELECT `id` FROM `test_items_table` WHERE `id`=0', 'id');
        $this->assertNotNull($items);
        $this->assertTrue(is_array($items));
        $this->assertCount(0, $items);
        
        $error = '';
        try {
            $db->getItems('SELECT `id` FROM `test_items_table` LIMIT 1', 'id2');
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $this->assertEquals('Undefined index: id2', $error);
    }
    
    public function testGetLastInsertedId()
    {
        $db = $this->getDb();
        $this->assertEquals(0, $db->getLastInsertedId());
    }
    
    public function testAddEscape()
    {
        $db = $this->getDb();
        $this->assertEquals('', $db->addEscape(''));
        $this->assertEquals('123', $db->addEscape('123'));
        $this->assertEquals('qwe', $db->addEscape('qwe'));
        $this->assertEquals(
            '123 qwe ,.?!<>/|[]{}()-=_+~`;:@#$%^&*', 
            $db->addEscape('123 qwe ,.?!<>/|[]{}()-=_+~`;:@#$%^&*')
        );
        $this->assertEquals("\t", $db->addEscape("\t"));
        $this->assertEquals('\\r\\n\\0\\Z', $db->addEscape("\r\n\0\x1A"));
        $this->assertEquals('\\\\', $db->addEscape('\\'));
        $this->assertEquals("\'", $db->addEscape("'"));
        $this->assertEquals('\"', $db->addEscape('"'));
    }
    
    public function testGetError()
    {
        $db = $this->getDb();
        $this->assertEquals('', $db->getError());
    }
}
