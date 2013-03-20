<?php

namespace Khameleon\Memory;

class File implements \Khameleon\File
{
    private
        $content,
        $name,
        $parent;
    
    public function __construct($name, Directory $parent)
    {
        $this->name = $name;
        $this->parent = $parent;
        $parent->attach($this);
        $this->content = null;
    }
    
    public function getPath()
    {
        return $this->parent->getPath() . DIRECTORY_SEPARATOR . $this->name;
    }
    
    public function getName()
    {
        return $this->name;
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
}