<?php

namespace Khameleon\Memory;

class Directory implements \Khameleon\Directory
{
    private
        $children,
        $name,
        $parent;
    
    public function __construct($name, Directory $parent = null)
    {
        $this->children = array();
        $this->name = $name;
        $this->parent = $parent;
        
        if($parent !== null)
        {
            $parent->attach($this);
        }
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
    
    public function read()
    {
        return new \ArrayIterator($this->children);
    }
    
    public function get($name)
    {
        if(isset($this->children[$name]))
        {
            return $this->children[$name];
        }
        
        throw new \Khameleon\Exceptions\Exception("$name does not exist in " . $this->getPath());
    }
    
    public function attach(\Khameleon\Node $node)
    {
        $this->children[$node->getName()] = $node;
    }
}