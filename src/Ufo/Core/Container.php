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
 * 
 */
class Container implements ContainerInterface
{
    /**
     * @param array $vars = null
     */
    public function __construct(array $vars = null)
    {
        if (is_null($vars)) {
            return;
        }
        foreach ($vars as $key => $val) {
            if (is_object($val) || is_array($val)) {
                /*
                 NOT
                 $this->$key =& $val;
                 because $val is reference
                 */
                $this->$key =& $vars[$key];
            } else {
                $this->$key = $val;
            }
        }
    }
    
    /**
     * Link property with reference.
     * @param string $property
     * @param object $reference
     */
    public function setByRef(string $property, &$reference): void
    {
        $this->$property =& $reference;
    }
    
    /**
     * Gets reference to property.
     * @param string $property
     * @return mixed
     */
    public function &getRef($property)
    {
        return $this->$property;
    }
    
    /**
     * Sets property with value.
     * @param string $property
     * @param mixed $value
     */
    public function set(string $property, $value): void
    {
        $this->$property = $value;
    }
    
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @param string $property
     * @return mixed
     */
    public function get(string $property)
    {
        // if (!property_exists($this, $property)) {
            // throw new NotFoundException();
        // }
        return $this->$property;
    }
    
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * @param string $property
     * @return bool
     */
    public function has(string $property): bool
    {
        return property_exists($this, $property);
    }
    
    /**
     * Returns all container entries.
     * @return array
     */
    public function getAll(): array
    {
        return get_object_vars($this);
    }
}
