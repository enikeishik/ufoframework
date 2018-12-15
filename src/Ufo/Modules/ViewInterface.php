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
 * Module level View base class interface.
 */
interface ViewInterface extends RenderableInterface
{
    /**
     * @param string $template
     * @param array $data = []
     */
    public function __construct(string $template, array $data = []);
    
    /**
     * @param string $template
     * @return void
     */
    public function setTemplate(string $template): void;
    
    /**
     * @return string
     */
    public function getTemplate(): string;
    
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
