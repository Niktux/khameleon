<?php

namespace Khameleon;

interface Directory extends Node, \Countable
{
    /**
     * @returns \Iterator
     */
    public function read();
    
    /**
     * @param string $name
     * @returns \Khameleon\Node
     * @throws \Khameleon\Exceptions\Exception
     */
    public function get($name);
}