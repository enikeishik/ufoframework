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
 * Module execution result.
 */
class Result implements ResultInterface
{
    /**
     * @var array
     */
    protected $headers = [];
    
    /**
     * @var string
     */
    protected $content = '';
    
    /**
     * @param string $content
     * @param array $headers = []
     */
    public function __construct(string $content, array $headers = [])
    {
        $this->content = $content;
        $this->headers = $headers;
    }
    
    /**
     * Sets content.
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
    
    /**
     * Returns generated content.
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
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
     * Changes content using callback function.
     * @param callable $callback
     * @return string
     */
    public function changeContent(callable $callback): string
    {
        return call_user_func($callback, $content);
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
