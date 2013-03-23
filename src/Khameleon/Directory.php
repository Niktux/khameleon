<?php

namespace Khameleon;

interface Directory extends Node, \Countable
{
    /**
     * @returns \Iterator
     */
    public function read();
    
    /**
     * @returns \Iterator
     */
    public function recursiveRead();
    
    /**
     * @param string $name
     * @returns \Khameleon\Node
     * @throws \Khameleon\Exceptions\Exception
     */
    public function get($name);
    
    /**
     * @returns boolean
     */
    public function isEmpty();
    
    /**
     * Recursive remove
     */
    public function removeDirectory();
}