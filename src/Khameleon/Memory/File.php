<?php

namespace Khameleon\Memory;

class File extends Node implements \Khameleon\File
{
    private
        $content;
    
    public function __construct(FileSystem $fs, $name, Directory $parent)
    {
        parent::__construct($fs, $name, $parent);
        
        $this->content = null;
    }
    
    public function read()
    {
        return $this->content;
    }
    
    public function write($content)
    {
        $this->content = $content;
        
        return $this;
    }
    
    public function detachFromParent()
    {
        $this->parent->detach($this);
    }
}