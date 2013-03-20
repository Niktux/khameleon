<?php

namespace Khameleon\Memory;

class Directory implements \Khameleon\Directory
{
    private
        $name,
        $parent;
    
    public function __construct($name, Directory $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }
    
    public function getPath()
    {
        $basePath = '';
        if($this->parent instanceof Directory)
        {
            $basePath = $this->parent->getPath() . DIRECTORY_SEPARATOR;
        }
        return rtrim($basePath . $this->name, DIRECTORY_SEPARATOR);
    }
    
    public function getName()
    {
        return $this->name;
    }
}