<?php
/**
 * UFO Framework.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ufo\Modules;

/**
 * Module level view base class interface.
 */
interface ViewInterface extends RenderableInterface
{
    /**
     * @param string $view
     * @param array $data = []
     */
    public function __construct(string $view, array $data = []);
    
    /**
     * @param string $view
     * @return void
     */
    public function setView(string $view): void;
    
    /**
     * @return string
     */
    public function getView(): string;
    
    /**
     * @param array $data
     * @return void
     */
    public function setData(string $data): void;
    
    /**
     * @return array
     */
    public function getData(): array;
}
