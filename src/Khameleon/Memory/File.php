<?php

namespace Khameleon\Memory;

class File implements \Khameleon\File
{
    private
        $fileSystem,
        $name,
        $parent,
        $content;
    
    public function __construct(FileSystem $fs, $name, Directory $parent)
    {
        $this->fileSystem = $fs;
        $this->name = $name;
        $this->parent = $parent;
        $this->content = null;
        
        $parent->attach($this);
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
    
    public function detachFromParent()
    {
        $this->parent->detach($this);
    }

    public function remove()
    {
        $this->fileSystem->remove($this->getPath());
    }
    
}