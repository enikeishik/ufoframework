<?php
use \Ufo\Core\Config;
use \Ufo\Core\Db;
use \Ufo\Core\Section;
use \Ufo\Widgets\WidgetsArrayStorage;
use \Ufo\Widgets\WidgetsDbStorage;
 
class WidgetsTest extends BaseUnitTest
{
    // tests
    public function testArrayStorage()
    {
        $storage = new WidgetsArrayStorage([]);
        $this->assertEquals([], $storage->getWidgets(new Section()));
        
        $storage = new WidgetsArrayStorage([
            '/' => [
                'test location 1' => [
                    ['module' => 'news', 'name' => '', 'title' => 'news widget', 'text' => 'news widget items'], 
                    ['module' => 'articles', 'name' => '', 'title' => 'articles widget', 'text' => 'articles widget items'], 
                ], 
            ], 
        ]);
        $this->assertEquals(
            [
                'test location 1' => [
                    ['module' => 'news', 'name' => '', 'title' => 'news widget', 'text' => 'news widget items'], 
                    ['module' => 'articles', 'name' => '', 'title' => 'articles widget', 'text' => 'articles widget items'], 
                ], 
            ], 
            $storage->getWidgets(new Section(['path' => '/']))
        );
        
        $storage = new WidgetsArrayStorage([
            '/' => [
                'test location 1' => [
                    ['module' => 'news', 'name' => '', 'title' => 'news widget', 'text' => 'news widget items'], 
                    ['module' => 'articles', 'name' => '', 'title' => 'articles widget', 'text' => 'articles widget items'], 
                ], 
            ], 
            '' => [
                'test location 1' => [
                    ['module' => 'gallery', 'name' => '', 'title' => 'gallery widget', 'text' => 'gallery widget items'], 
                ], 
            ], 
        ]);
        $this->assertEquals(
            [
                'test location 1' => [
                    ['module' => 'gallery', 'name' => '', 'title' => 'gallery widget', 'text' => 'gallery widget items'], 
                    ['module' => 'news', 'name' => '', 'title' => 'news widget', 'text' => 'news widget items'], 
                    ['module' => 'articles', 'name' => '', 'title' => 'articles widget', 'text' => 'articles widget items'], 
                ], 
            ], 
            $storage->getWidgets(new Section(['path' => '/']))
        );
    }

    public function testDbStorage()
    {
        $config = new Config();
        $config->loadFromIni(dirname(__DIR__) . '/_data/.config', true);
        $db = Db::getInstance($config);
        $storage = new WidgetsDbStorage($db);
        $this->assertEquals([], $storage->getWidgets(new Section()));
        $this->assertEquals([], $storage->getWidgets(new Section(['path' => '/'])));
    }
}
