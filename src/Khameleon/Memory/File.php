<?php

namespace Khameleon\Memory;

class File implements \Khameleon\File
{
    private
        $name,
        $parent;
    
    public function __construct($name, Directory $parent)
    {
        $this->name = $name;
        $this->parent = $parent;
    }
    
    public function getPath()
    {
        return $this->parent->getPath() . DIRECTORY_SEPARATOR . $this->name;
    }
    
    public function getName()
    {
        return $this->name;
    }
}