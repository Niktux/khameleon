<?php

namespace Khameleon;

interface File extends Node
{
    /**
     * @returns string
     */
    public function read();
    
    /**
     * @param string $content
     */
    public function write($content);
}