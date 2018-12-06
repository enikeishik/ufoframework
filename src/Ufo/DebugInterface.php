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
 * Dubug application interface.
 */
interface DebugInterface
{
    /**
     * @return float
     */
    public function getExecutionTime(): float;    
    
    /**
     * @param string $operation
     * @return int
     */
    public function trace(string $operation): int;
    
    /**
     * @param int $idx = null
     * @param int $errCode = null
     * @param string $errMessage = null
     * @return void
     * @throws DebugIndexNotExistsException
     */
    public function traceClose(int $idx = null, int $errCode = null, string $errMessage = null): void;
    
    /**
     * @return void
     */
    public function traceEnd(): void;
    
    /**
     * @return int
     */
    public function getTraceCount(): int;
    
    /**
     * @return array
     */
    public function getTrace(): array;
    
    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @return bool
     */
    public static function errorHandler(int $errno, string $errstr, string $errfile, string $errline): bool;
    
    /**
     * @return array
     */
    public static function getErrors(): array;
}
