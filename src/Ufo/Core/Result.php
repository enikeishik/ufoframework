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
 * Module execution result.
 */
class Result implements ResultInterface
{
    /**
     * @var \Ufo\Modules\RenderableInterface
     */
    protected $view = '';
    
    /**
     * @var array
     */
    protected $headers = [];
    
    /**
     * @param \Ufo\Modules\RenderableInterface $view
     * @param array $headers = []
     */
    public function __construct(RenderableInterface $view, array $headers = [])
    {
        $this->view = $view;
        $this->headers = $headers;
    }
    
    /**
     * Sets content.
     * @param \Ufo\Modules\RenderableInterface $view
     * @return void
     */
    public function setView(RenderableInterface $view): void
    {
        $this->view = $view;
    }
    
    /**
     * Returns generated content.
     * @return \Ufo\Modules\RenderableInterface $view
     */
    public function getView(): RenderableInterface
    {
        return $this->view;
    }
    
    /**
     * Sets headers.
     * @param array $headers
     * @return void
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
    
    /**
     * Returns all generated headers.
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * Finds an header by its name and returns it.
     * @param string $property
     * @return mixed
     */
    public function getHeader(string $name): string
    {
        // if (!array_key_exists($name, $this->headers)) {
            // throw new NotFoundException();
        // }
        return $this->headers[$name];
    }
    
    /**
     * Returns true if header with the name exists.
     * Returns false otherwise.
     * @param string $property
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }
    
    /**
     * Changes header value.
     * @param string $name
     * @param string $value
     * @return void
     */
    public function changeHeader(string $name, string $value): void
    {
        // if (!array_key_exists($name, $this->headers)) {
            // throw new NotFoundException();
        // }
        $this->headers[$name] = $value;
    }
    
    /**
     * Changes headers using callback function.
     * @param callable $callback
     * @return void
     */
    public function changeHeaders(callable $callback): void
    {
        foreach ($this->headers as $name => $value) {
            $this->headers[$name] = call_user_func($callback, $name, $value);
        }
    }
}
