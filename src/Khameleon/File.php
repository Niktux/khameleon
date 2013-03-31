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
    
    /**
     * Returns creation timestamp
     *
     * @returns int timestamp
     */
    public function getCreationTime();
    
    /**
     * Returns last access timestamp
     *
     * @returns int timestamp
     */
    public function getAccessTime();
    
    /**
     * Returns last modification timestamp
     *
     * @returns int timestamp
     */
    public function getModificationTime();
    
    /**
     * Returns filesize in bytes
     *
     * @return int
     */
    public function getSize();
    
    /**
     * Returns file extension
     *
     * @returns string
     */
    public function getExtension();
}