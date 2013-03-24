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
     * @param string $relativePath
     * @return \Khameleon\File
     * @throws \Khameleon\Exceptions\AlreadyExistingNodeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function putFile($relativePath);
    
    /**
     * @param string $relativePath
     * @return \Khameleon\Directory
     * @throws \Khameleon\Exceptions\AlreadyExistingNodeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function putDirectory($relativePath);
}