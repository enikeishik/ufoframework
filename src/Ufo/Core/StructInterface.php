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
 * Structure interface.
 */
interface StructInterface
{
    /**
     * Load data into class fields from array|object|JSON.
     *
     * @param mixed $vars = null
     * @param bool $cast = true
     */
    public function __construct($vars = null, bool $cast = true);
    
    /**
     * String representation of current structure.
     * 
     * @return string
     */
    public function __toString(): string;
    
    /**
     * Sets $this fields values from the same $struct fields values.
     * 
     * @param Struct $struct
     */
    public function set(StructInterface $struct): void;
    
    /**
     * Sets $this fields values from $vars associated array.
     * 
     * @param array $vars
     * @param bool $cast = true
     */
    public function setFromArray(array $vars, bool $cast = true): void;
    
    /**
     * Gets all public fields and its values as associated array.
     * 
     * @return array<string $key => mixed $value>
     */
    public function getArray(): array;
    
    /**
     * Gets array of public fields (names).
     * 
     * @return array
     */
    public function getFields(): array;
}
