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
     * @param string $path
     * @return \Khameleon\File
     * @throws \Khameleon\Exceptions\WrongNodeTypeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function putFile($relativePath);
    
    /**
     * @param string $path
     * @return \Khameleon\Directory
     * @throws \Khameleon\Exceptions\WrongNodeTypeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function putDirectory($relativePath);
}