<?php
use \Ufo\Core\Config;
use \Ufo\Core\Container;
use \Ufo\Core\Module;
use \Ufo\Core\Section;
use \Ufo\Modules\View;
 
class ViewTest extends BaseUnitTest
{
    protected function getView($template, $data)
    {
        return new class($template, $data) extends View {
            public $template = '';
            public $data = [];
            public $widgets = [];
        };
    }
    
    
    // tests
    public function testView()
    {
        $view = $this->getView('test.template', ['testVar' => 'test value']);
        $this->assertEquals('test.template', $view->template);
        $this->assertEquals('test.template', $view->getTemplate());
        $this->assertEquals(['testVar' => 'test value'], $view->data);
        $this->assertEquals(['testVar' => 'test value'], $view->getData());
        
        $view->setTemplate('another.test.template');
        $this->assertEquals('another.test.template', $view->template);
        $this->assertEquals('another.test.template', $view->getTemplate());
        
        $view->setData(['anotherTestVar' => 'another test value']);
        $this->assertEquals(['anotherTestVar' => 'another test value'], $view->data);
        $this->assertEquals(['anotherTestVar' => 'another test value'], $view->getData());
        
        $view->setWidgets([1, 2]);
        $this->assertEquals([1, 2], $view->widgets);
        $this->assertEquals([1, 2], $view->getWidgets());
        
        $config = new Config();
        $config->templatesPath = dirname(__DIR__) . '/integration/templates';
        $config->templatesDefault = '';
        $view->inject(new Container(['config' => $config]));
        $this->expectedException(
            PHPUnit\Framework\Exception::class, 
            function() use($view) { $view->render(); }
        );
        
        $view = $this->getView(
            'index', 
            [
                'content' => 'test content', 
                'section' => new Section(['title' => 'test title'])
            ]
        );
        $view->inject(new Container(['config' => $config]));
        $content = $view->render();
        $this->assertContains('<title>test title</title>', $content);
        $this->assertContains('test content', $content);
        
        $config = new Config();
        $config->templatesPath = dirname(__DIR__) . '/integration';
        $config->templatesDefault = '';
        $section = new Section([
            'title' => 'test title', 
            'module' => new Module(['name' => 'templates'])
        ]);
        $view = $this->getView(
            'index', 
            [
                'content' => 'test content', 
                'section' => $section
            ]
        );
        $view->inject(new Container(['config' => $config, 'section' => $section]));
        $content = $view->render();
        $this->assertContains('<title>test title</title>', $content);
        $this->assertContains('test content', $content);
    }
}
