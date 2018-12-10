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
 * Abstract implementation of structure.
 */
abstract class Struct implements StructInterface
{
    /**
     * Load data into class fields from array|object|JSON.
     *
     * @param mixed $vars = null
     * @param bool $cast = true
     */
    public function __construct($vars = null, bool $cast = true)
    {
        if (is_array($vars)) {
            $this->setFromArray($vars, $cast);
        } elseif (is_object($vars)) {
            if (is_a($vars, __CLASS__)) {
                $this->set($vars);
            } else {
                $this->setFromArray(get_object_vars($vars), $cast);
            }
        } elseif (is_string($vars)) {
            $this->setFromArray(json_decode($vars, true), $cast);
        }
    }
    
    /**
     * String representation of current structure.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this);
    }
    
    /**
     * Sets $this fields values from the same $struct fields values.
     * 
     * @param StructInterface $struct
     */
    public function set(StructInterface $struct): void
    {
        $vars = get_object_vars($struct);
        foreach ($vars as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }
    
    /**
     * Sets $this fields values from $vars associated array.
     * 
     * @param array $vars
     * @param bool $cast = true
     */
    public function setFromArray(array $vars, bool $cast = true): void
    {
        if ($cast) {
            foreach ($vars as $key => $val) {
                if (property_exists($this, $key)) {
                    if (is_int($this->$key)) {
                        $this->$key = (int) $val;
                    } elseif (is_string($this->$key)) {
                        $this->$key = (string) $val;
                    } elseif (is_bool($this->$key)) {
                        $this->$key = (bool) $val;
                    } elseif (is_float($this->$key)) {
                        $this->$key = (float) $val;
                    } else {
                        $this->$key = $val;
                    }
                }
            }
        } else {
            foreach ($vars as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->$key = $val;
                }
            }
        }
    }
    
    /**
     * Gets all public fields and its values as associated array.
     * 
     * @return array<string $key => mixed $value>
     */
    public function getArray(): array
    {
        return get_object_vars($this);
    }
    
    /**
     * Gets array of public fields (names).
     * 
     * @return array
     */
    public function getFields(): array
    {
        return array_keys($this->getArray());
    }
}
