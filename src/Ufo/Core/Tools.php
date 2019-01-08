<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

/**
 * Tools.
 */
class Tools
{
    /**
     * @param string $str
     * @param bool $closingSlashRequired = false
     * @return bool
     */
    public static function isPath(string $str, bool $closingSlashRequired = false): bool
    {
        if ($closingSlashRequired) {
            return (1 == preg_match('/^\/[a-z0-9~_\/\-\.]+\/$/i', $str)
                    && 0 == preg_match('/(\/{2})|(\.{2})/i', $str));
        }
        return (1 == preg_match('/^\/[a-z0-9~_\/\-\.]+$/i', $str)
                && 0 == preg_match('/(\/{2})|(\.{2})/i', $str));
    }
    
    /**
     * @param mixed $str
     * @param bool $unsigned = false
     * @return bool
     */
    public static function isInt($str, bool $unsigned = false): bool
    {
        if (!$unsigned) {
            return strlen((string) (int) $str) == strlen((string) $str);
        } else {
            return ctype_digit((string) $str) && ($str <= PHP_INT_MAX);
        }
    }
    
    /**
     * @param array $arr
     * @return bool
     */
    public static function isArrayOfIntegers(array $arr): bool
    {
        foreach ($arr as $val) {
            if (!self::isInt($val)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * @param array $arr
     * @return array<int>
     */
    public static function getArrayOfIntegers(array $arr): array
    {
        return array_map(
            function($str) { return (int) trim($str); }, 
            $arr
        );
    }
    
    /**
     * @param string $str
     * @param string $sep
     * @return boolean
     */
    public static function isStringOfIntegers(string $str, string $sep = ','): bool
    {
        return self::isArrayOfIntegers(
            array_map(
                function($str) { return trim($str); }, 
                explode($sep, $str)
            )
        );
    }
    
    /**
     * @param string $str
     * @param string $sep
     * @return array<int>
     */
    public static function getArrayOfIntegersFromString(string $str, string $sep = ','): array
    {
        return self::getArrayOfIntegers(explode($sep, $str));
    }
    
    /**
     * @param string $str
     * @return boolean
     */
    public static function isEmail(string $str): bool
    {
        if (0 == strlen($str)) {
            return false;
        }
        return (bool) preg_match('/[a-z0-9_\-\.]+@[a-z0-9\-\.]{2,}\.[a-z]{2,6}/i', $str);
    }
    
    /**
     * @param string $str
     * @param bool $rawHtml = false
     * @return string
     */
    public static function getSafeJsString(string $str, bool $rawHtml = false): string
    {
        if (!$rawHtml) {
            return htmlspecialchars(addcslashes($str, "\0..\37\"\'\\"), ENT_NOQUOTES);
        } else {
            return addcslashes($str, "\0..\37\"\'\\");
        }
    }
}
