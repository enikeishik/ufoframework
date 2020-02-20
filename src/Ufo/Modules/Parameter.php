<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Modules;

use PhpStrict\Struct\Struct;

/**
 * Module level parameter structure class.
 */
class Parameter extends Struct
{
    /**
     * @var string
     */
    public $name = '';
    
    /**
     * @var string bool|int|date|string|user defined
     */
    public $type = '';
    
    /**
     * Values: none - some internal flags, path - gets from path, get - gets from $_GET
     * @var string path|get
     */
    public $from = '';
    
    /**
     * Parameter prefix for from=path, or name for from=get.
     * @var string
     */
    public $prefix = '';
    
    /**
     * Values:
     * false - must be only one (no other params can be set, except additional),
     * true - can be set with other params.
     * @var bool
     */
    public $additional = false;
    
    /**
     * Default value of parameter.
     * @var mixed
     */
    public $defval = null;
    
    /**
     * Value of parameter.
     * @var mixed
     */
    public $value = null;
    
    /**
     * Validator for user defined type parameter.
     * @var callable
     */
    public $validator = null;
    
    /**
     * @param string $name
     * @param string $type
     * @param string $prefix
     * @param string $from = 'path'
     * @param bool $additional = false
     * @param mixed $default = null
     * @param mixed $value = null
     * @param callable $validator = null
     * @return self
     */
    public static function make(
        string $name, 
        string $type, 
        string $prefix, 
        string $from = 'path', 
        bool $additional = false, 
        $defval = null, 
        $value = null, 
        callable $validator = null
    ): self {
        $parameter = new self();
        $parameter->name        = $name;
        $parameter->type        = $type;
        $parameter->prefix      = $prefix;
        $parameter->from        = $from;
        $parameter->additional  = $additional;
        $parameter->defval      = $defval;
        $parameter->value       = $value;
        $parameter->validator   = $validator;
        return $parameter;
    }
    
    /**
     * @param string $name
     * @param string $prefix
     * @param string $from = 'path'
     * @param bool $additional = false
     * @param mixed $default = null
     * @param mixed $value = null
     * @return self
     */
    public static function makeBool(
        string $name, 
        string $prefix, 
        string $from = 'path', 
        bool $additional = false, 
        $defval = null, 
        $value = null
    ): self {
        return self::make($name, 'bool', $prefix, $from, $additional, $defval, $value);
    }
    
    /**
     * @param string $name
     * @param string $prefix
     * @param string $from = 'path'
     * @param bool $additional = false
     * @param mixed $default = null
     * @param mixed $value = null
     * @return self
     */
    public static function makeInt(
        string $name, 
        string $prefix, 
        string $from = 'path', 
        bool $additional = false, 
        $defval = null, 
        $value = null
    ): self {
        return self::make($name, 'int', $prefix, $from, $additional, $defval, $value);
    }
    
    /**
     * @param string $name
     * @param string $prefix
     * @param string $from = 'path'
     * @param bool $additional = false
     * @param mixed $default = null
     * @param mixed $value = null
     * @return self
     */
    public static function makeString(
        string $name, 
        string $prefix, 
        string $from = 'path', 
        bool $additional = false, 
        $defval = null, 
        $value = null
    ): self {
        return self::make($name, 'string', $prefix, $from, $additional, $defval, $value);
    }
    
    /**
     * @param string $name
     * @param string $prefix
     * @param string $from = 'path'
     * @param bool $additional = false
     * @param mixed $default = null
     * @param mixed $value = null
     * @return self
     */
    public static function makeDate(
        string $name, 
        string $prefix, 
        string $from = 'path', 
        bool $additional = false, 
        $defval = null, 
        $value = null
    ): self {
        return self::make($name, 'date', $prefix, $from, $additional, $defval, $value);
    }
    
    /**
     * @param string $name
     * @param string $prefix
     * @param string $from = 'path'
     * @param bool $additional = false
     * @param mixed $default = null
     * @param mixed $value = null
     * @param callable $validator = null
     * @return self
     */
    public function makeUserType(
        string $name, 
        string $prefix, 
        string $from = 'path', 
        bool $additional = false, 
        $defval = null, 
        $value = null, 
        callable $validator = null
    ): self {
        return self::make($name, 'date', $prefix, $from, $additional, $defval, $value, $validator);
    }
}
