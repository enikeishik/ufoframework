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
 * Describes the interface of a container that exposes methods to read its entries.
 */
interface ContainerInterface //extends \Psr\Container\ContainerInterface
{
    /**
     * @param array $vars = null
     */
    public function __construct(array $vars = null);
    
    /**
     * Link property with reference.
     * @param string $property
     * @param object $reference
     */
    public function setByRef(string $property, &$reference): void;
    
    /**
     * Gets reference to property.
     * @param string $property
     * @return mixed
     */
    public function &getRef(string $property);
    
    /**
     * Sets property with value.
     * @param string $property
     * @param mixed $value
     */
    public function set(string $property, $value): void;
    
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @param string $property
     * @return mixed
     */
    public function get(string $property);
    
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * @param string $property
     * @return bool
     */
    public function has(string $property): bool;
    
    /**
     * Returns all container entries.
     * @return array
     */
    public function getAll(): array;
}
