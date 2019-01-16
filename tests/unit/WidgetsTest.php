<?php
use \Ufo\Core\Section;
use \Ufo\Widgets\WidgetsArrayStorage;
 
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
}
