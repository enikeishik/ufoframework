<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Core;

use Ufo\Modules\RenderableInterface;

/**
 * Describes the interface of a module execution result.
 */
interface ResultInterface
{
    /**
     * @param \Ufo\Modules\RenderableInterface $view
     * @param array $headers = []
     */
    public function __construct(RenderableInterface $view, array $headers = []);
    
    /**
     * Sets content.
     * @param \Ufo\Modules\RenderableInterface $view
     * @return void
     */
    public function setView(RenderableInterface $view): void;
    
    /**
     * Returns generated content.
     * @return \Ufo\Modules\RenderableInterface $view
     */
    public function getView(): RenderableInterface;
    
    /**
     * Sets headers.
     * @param array $headers
     * @return void
     */
    public function setHeaders(array $headers): void;
    
    /**
     * Returns all generated headers.
     * @return array
     */
    public function getHeaders(): array;
    
    /**
     * Finds an header by its name and returns it.
     * @param string $property
     * @return mixed
     */
    public function getHeader(string $name): string;
    
    /**
     * Returns true if header with the name exists.
     * Returns false otherwise.
     * @param string $property
     * @return bool
     */
    public function hasHeader(string $name): bool;
    
    /**
     * Changes header value.
     * @param string $name
     * @param string $value
     * @return void
     */
    public function changeHeader(string $name, string $value): void;
    
    /**
     * Changes headers using callback function.
     * @param callable $callback
     * @return void
     */
    public function changeHeaders(callable $callback): void;
}
