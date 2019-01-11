<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use \Ufo\Core\Tools;
 
class ToolsTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    /**
     * @var Tools
     */
    protected $tools;
    
    protected function _before()
    {
        $this->tools = new Tools();
    }
    
    protected function _after()
    {
        $this->tools = null;
    }
    
    // tests
    public function isPathDataProvider()
    {
        return [
            ['', false, false], 
            ['/', false, false], 
            ['/!', false, false], 
            ['//', false, false], 
            ['/..', false, false], 
            ['/123+', false, false], 
            
            ['/../', true, false], 
            ['/123', true, false], 
            
            ['/123', false, true], 
            ['/123/', false, true], 
            ['/123/asd', false, true], 
            ['/123/asd/', false, true], 
            ['/12-3/as_d/~qwe.htm', false, true], 
            
            ['/123/', true, true], 
            ['/123/asd/', true, true], 
            ['/12-3/as_d/~qwe.htm/', true, true], 
        ];
    }
    
    /**
     * @dataProvider isPathDataProvider
     */
    public function testIsPath($val, $closingSlashRequired, $expected)
    {
        $result = $this->tools->isPath($val, $closingSlashRequired);
        $this->assertEquals($expected, $result);
    }
    
    public function isIntDataProvider()
    {
        return [
            //signed int
            ['0', false, true], 
            ['1', false, true], 
            ['-1', false, true], 
            [(string) (-PHP_INT_MAX - 1), false, true], 
            [(string) PHP_INT_MAX, false, true], 
            
            //not signed int
            [(string) (PHP_INT_MAX + 1), false, false], 
            [(string) (-PHP_INT_MAX - 2), false, false], 
            ['1.0', false, false], 
            ['1a', false, false], 
            ['a', false, false], 
            ['', false, false], 
            
            //unsigned int
            ['0', true, true], 
            ['1', true, true], 
            [(string) PHP_INT_MAX, true, true], 
            
            //not unsigned int
            ['-1', true, false], 
            [(string) (-PHP_INT_MAX - 1), true, false], 
            ['1.0', true, false], //'1.0' -> not int, but 1.0 -> int!
            ['1a', true, false], 
            ['a', true, false], 
            ['', true, false], 
            ['-0', true, false], 
        ];
    }   
    
    /**
     * @dataProvider isIntDataProvider
     */
    public function testIsInt($val, $unsigned, $expected)
    {
        $result = $this->tools->isInt($val, $unsigned);
        $this->assertEquals($expected, $result);
    }
    
    public function isArrayOfIntegersDataProvider()
    {
        return [
            [[0, 1, 2], true], 
            [[-1, 0, 1], true], 
            [[0, -0], true], 
            [[0, 1.0, 2], true], //'1.0' -> not int, but 1.0 -> int!
            [[0, '1', 2], true], 
            [[0, PHP_INT_MAX], true], 
            [[PHP_INT_MAX * -1 - 1, PHP_INT_MAX], true], 
            
            [[0, 1.1, 2], false], 
            [[0, '1a', 2], false], 
            [[0, PHP_INT_MAX + 1], false], 
            [[PHP_INT_MAX * -1 - 2, PHP_INT_MAX], false], 
        ];
    }
    
    /**
     * @dataProvider isArrayOfIntegersDataProvider
     */
    public function testIsArrayOfIntegers($val, $expected)
    {
        $result = $this->tools->isArrayOfIntegers($val);
        $this->assertEquals($expected, $result);
    }
    
    public function getArrayOfIntegersDataProvider()
    {
        return [
            [['0', '1', '2'], [0, 1, 2]], 
            [['-0', '-1', '-2'], [0, -1, -2]], 
            [[-0, -1, -2], [0, -1, -2]], 
            [['0', 1, '2'], [0, 1, 2]], 
            [['0', 1.0, '2'], [0, 1, 2]], 
            [['0', 1.1, '2'], [0, 1, 2]], 
            [['0', '1a', '2'], [0, 1, 2]], 
            [['0', 'a', '2'], [0, 0, 2]], 
            [['0', '', '2'], [0, 0, 2]], 
            [['0', ' 1', '2'], [0, 1, 2]], 
            [['0', ' 1 ', '2'], [0, 1, 2]], 
            [[0, PHP_INT_MAX], [0, PHP_INT_MAX]], 
            [[0, PHP_INT_MAX + 1], [0, PHP_INT_MAX]], 
            [[0, PHP_INT_MAX * -1 - 1], [0, PHP_INT_MAX * -1 - 1]], 
            [[0, PHP_INT_MAX * -1 - 2], [0, PHP_INT_MAX * -1 - 1]], 
        ];
    }
    
    /**
     * @dataProvider getArrayOfIntegersDataProvider
     */
    public function testGetArrayOfIntegers($val, $expected)
    {
        $arr = $this->tools->getArrayOfIntegers($val);
        $this->assertEquals($expected, $arr);
    }
    
    public function isStringOfIntegersDataProvider()
    {
        return [
            ['1,2,3', ',', true], 
            ['0, 1, 2', ',', true], 
            ['-1, 0, 1', ',', true], 
            [(PHP_INT_MAX * -1 - 1) . ', 0, ' . PHP_INT_MAX, ',', true], 
            ['1 2 3', ' ', true], 
            
            ['1,2a,3', ',', false], 
            ['1.0, 2, 3', ',', false], 
            [(PHP_INT_MAX * -1 - 2) . ', 0, 1', ',', false], 
            ['-1, 0, ' . (PHP_INT_MAX + 1), ',', false], 
            ['1; 2; 3', ',', false], 
        ];
    }
    
    /**
     * @dataProvider isStringOfIntegersDataProvider
     */
    public function testIsStringOfIntegers($val, $sep, $expected)
    {
        $result = $this->tools->isStringOfIntegers($val, $sep);
        $this->assertEquals($expected, $result);
    }
    
    public function getArrayOfIntegersFromStringDataProvider()
    {
        return [
            ['0, 1, 2', ',', [0, 1, 2]], 
            ['-1, 0, 1', ',', [-1, 0, 1]], 
            ['0 1 2', ' ', [0, 1, 2]], 
            ['0, 1, 2, ' . PHP_INT_MAX, ',', [0, 1, 2, PHP_INT_MAX]], 
            [(PHP_INT_MAX * -1 - 1) . ', -1, 0, 1', ',', [(PHP_INT_MAX * -1 - 1), -1, 0, 1]], 
            
            ['0, 1.0, 2', ',', [0, 1, 2]], 
            ['0, 1.1, 2', ',', [0, 1, 2]], 
            ['0, 1a, 2', ',', [0, 1, 2]], 
            ['0, 1, 2, ' . (PHP_INT_MAX + 1), ',', [0, 1, 2, PHP_INT_MAX]], 
            [(PHP_INT_MAX * -1 - 2) . ', -1, 0, 1', ',', [PHP_INT_MAX * -1 - 1, -1, 0, 1]], 
            ['0, a, 2', ',', [0, 0, 2]], 
            ['0, , 2', ',', [0, 0, 2]], 
            ['0,,2', ',', [0, 0, 2]], 
            ['0,true,2', ',', [0, 0, 2]], 
            ['0,null,2', ',', [0, 0, 2]], 
        ];
    }
    
    /**
     * @dataProvider getArrayOfIntegersFromStringDataProvider
     */
    public function testGetArrayOfIntegersFromString($val, $sep, $expected)
    {
        $arr = $this->tools->getArrayOfIntegersFromString($val, $sep);
        $this->assertEquals($expected, $arr);
    }
    
    public function isEmailDataProvider()
    {
        return [
            ['', false], 
            ['aa@aa.aa', true], 
            ['aa-aa@aa.aa', true], 
            ['aa@aa-aa.aa', true], 
            ['aa@aa.aaaaaa', true], 
            ['a.a@aa.aa', true], 
            ['aa@a.a.aa', true], 
            ['aaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaa', true], 
            ['aa@aa.aa', true], 
            ['aaaa.aa', false], 
        ];
    }
    
    /**
     * @dataProvider isEmailDataProvider
     */
    public function testIsEmail($val, $expected)
    {
        $this->assertEquals($expected, $this->tools->isEmail($val));
    }
    
    public function getSafeJsStringDataProvider()
    {
        return [
            ['', true, ''], 
            ['', false, ''], 
            ['<br>', true, '<br>'], 
            ['<br>', false, '&lt;br&gt;'], 
            ['var jsVar = "var value<br>";', true, 'var jsVar = \"var value<br>\";'], 
            ['var jsVar = "var value<br>";', false, 'var jsVar = \"var value&lt;br&gt;\";'], 
            ['var jsVar = "var value<br>";', true, 'var jsVar = \"var value<br>\";'], 
            ['var jsVar = "var ' . "\'expression <script> \'" . '";', false, 'var jsVar = \"var ' . "\\\\\'expression &lt;script&gt; \\\\\'" . '\";'], 
            ['var jsVar = "var ' . "\'expression <script> \'" . '";', true, 'var jsVar = \"var ' . "\\\\\'expression <script> \\\\\'" . '\";'], 
            ["\n", false, '\n'], 
            ["\r", false, '\r'], 
            ["\t", false, '\t'], 
        ];
    }
    
    /**
     * @dataProvider getSafeJsStringDataProvider
     */
    public function testGetSafeJsString($val, $rawHtml, $expected)
    {
        $this->assertEquals($expected, $this->tools->getSafeJsString($val, $rawHtml));
    }
}
