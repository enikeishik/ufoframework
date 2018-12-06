<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo;

/**
 * Dubug application class.
 */
class Debug implements DebugInterface
{
    /**
     * @var int
     */
    protected const C_DEBUG_LEVEL = 1;
    
    /**
     * @var int
     */
    protected $debugStartTime = null;
    
    /**
     * @var array
     */
    protected $buffTrace = array();
    
    /**
     * @var array
     */
    protected static $buffErr = array();
    
    
    public function __construct()
    {
        $this->debugStartTime = microtime(true);
    }
    
    /**
     * @return float
     */
    public function getExecutionTime(): float
    {
        return microtime(true) - $this->debugStartTime;
    }
    
    /**
     * Create new trace item.
     * @param string $operation
     * @return int
     */
    public function trace(string $operation): int
    {
        $this->buffTrace[] = [
            'operation' => $operation, 
            'time'      => microtime(true), 
            'result'    => '', 
            'stack'     => 9 == self::C_DEBUG_LEVEL ? debug_backtrace(): null, 
        ];
        return count($this->buffTrace) - 1;
    }
    
    /**
     * Close trace item by its index.
     * @param int $idx = null
     * @param int $errCode = null
     * @param string $errMessage = null
     * @return void
     */
    public function traceClose(int $idx = null, int $errCode = null, string $errMessage = null): void
    {
        if (null === $idx) {
            $idx = count($this->buffTrace) - 1;
        }
        
        if (!array_key_exists($idx, $this->buffTrace)) {
            throw new DebugIndexNotExistsException();
        }
        
        $this->buffTrace[$idx]['time'] = round(microtime(true) - $this->buffTrace[$idx]['time'], 4);
        $this->buffTrace[$idx]['result'] = (null === $errCode ? 'OK' : '(' . $errCode . ') ' . $errMessage);
    }
    
    /**
     * Set time to now-time for each unclosed trace items.
     * @return void
     */
    public function traceEnd(): void
    {
        $now = microtime(true);
        foreach ($this->buffTrace as &$traceItem) {
            if ('' === $traceItem['result']) {
                $traceItem['time'] = round($now - $traceItem['time'], 4);
                $traceItem['result'] = 'Trace end';
            }
        }
        unset($traceItem);
    }
    
    /**
     * @return int
     */
    public function getTraceCount(): int
    {
        return count($this->buffTrace);
    }
    
    /**
     * @return array
     */
    public function getTrace(): array
    {
        return $this->buffTrace;
    }
    
    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @return bool
     */
    public static function errorHandler(int $errno, string $errstr, string $errfile, string $errline): bool
    {
        self::$buffErr[] = $errno . "\t" . $errfile . "\t" . $errline . "\t" . $errstr;
    }
    
    /**
     * @return array
     */
    public static function getErrors(): array
    {
        return self::$buffErr;
    }
    
    /**
     * Вывод информации о переменной.
     * @param mixed $var
     * @param bool $dump = true
     * @param bool $exit = true
     * @param bool $float = false
     * @return void
     */
    public static function varDump($var, bool $dump = true, bool $exit = true, bool $float = false): void
    {
        echo '<pre' . ($float ? ' class="debugfloat"' : '') . '>';
        ob_start();
        $dump ? var_dump($var) : print_r($var);
        echo htmlspecialchars(str_replace("=>\n", '  =>', ob_get_clean()));
        echo '</pre>';
        if ($exit) {
            exit();
        }
    }
    
    /**
     * @see varDump
     */
    public static function vd($var, bool $dump = true, bool $exit = true, bool $float = false): void
    {
        self::varDump($var, $dump, $exit, $float);
    }
}
