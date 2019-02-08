<?php
use \Ufo\Core\Widget;
use \Ufo\Modules\ServiceProvider;
 
class ServiceProviderTest extends BaseUnitTest
{
    protected function getSP($jsonFile = 'sptestcomposer.json')
    {
        return new class($jsonFile) extends ServiceProvider {
            protected $jsonFile = '';
            public function __construct($jsonFile)
            {
                $this->jsonFile = $jsonFile;
            }
            protected function getModulePath(): string
            {
                parent::getModulePath();
                return dirname(__DIR__) . '/_data/' . $this->jsonFile;
            }
            protected function getWidgetsPath(): string
            {
                parent::getWidgetsPath();
                return dirname(__DIR__) . '/_data/sptestwidget*.php';
            }
            protected function getSqlDumpPath(): string
            {
                parent::getSqlDumpPath();
                return dirname(__DIR__) . '/_data/sptest.sql';
            }
            protected function getRootPath(): string
            {
                parent::getRootPath();
                return dirname(__DIR__) . '/_data';
            }
        };
    }
    
    // tests
    public function testGetModule()
    {
        $sp = $this->getSP();
        $m0 = $sp->getModule();
        $m = $sp->getModule();
        $this->assertEquals('sptestvendor', $m->vendor);
        $this->assertEquals('sptestname', $m->name);
        $this->assertFalse($m->dbless);
        $this->assertNull($m->callback);
        $this->assertFalse($m->disabled);
        
        $sp = $this->getSP('sptestcomposer-non-existence.json');
        $this->expectedException(
            \Exception::class, 
            function() use($sp) { $sp->getModule(); }
        );
        
        $sp = $this->getSP('sptestcomposerbad.json');
        $this->expectedException(
            \Exception::class, 
            function() use($sp) { $sp->getModule(); }
        );
        
        $sp = $this->getSP('sptestcomposerbad2.json');
        $this->expectedException(
            \Exception::class, 
            function() use($sp) { $sp->getModule(); }
        );
    }
    
    public function testGetWidgets()
    {
        $wd1 = include dirname(__DIR__) . '/_data/sptestwidget1.php';
        $wd2 = include dirname(__DIR__) . '/_data/sptestwidget2.php';
        $sp = $this->getSP();
        $widgets = $sp->getWidgets();
        $this->assertEquals(
            [
                new Widget($wd1), 
                new Widget($wd2), 
            ], 
            $widgets
        );
    }
    
    public function testGetSqlDump()
    {
        $sp = $this->getSP();
        $this->assertEquals('SELECT * FROM `sptest`;', $sp->getSqlDump());
        
        $sp = new class() extends ServiceProvider {
            protected function getSqlDumpPath(): string
            {
                return dirname(__DIR__) . '/_data/sptest-non-existence.sql';
            }
        };
        $this->assertNull($sp->getSqlDump());
        
        $sp = new class() extends ServiceProvider {
            protected function isSqlDumpExists(): bool
            {
                return true;
            }
            protected function getSqlDumpPath(): string
            {
                return dirname(__DIR__) . '/_data/sptest-non-existence.sql';
            }
        };
        $this->expectedException(
            \Exception::class, 
            function() use($sp) { $sp->getSqlDump(); }
        );
    }
}
