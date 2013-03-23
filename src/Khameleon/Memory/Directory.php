<?php

namespace Khameleon\Memory;

use Khameleon\Exceptions\NodeNotFoundException;

class Directory implements \Khameleon\Directory
{
    private
        $fileSystem,
        $name,
        $parent,
        $children;
    
    public function __construct(FileSystem $fs, $name, Directory $parent = null)
    {
        $this->fileSystem = $fs;
        $this->name = $name;
        $this->parent = $parent;
        $this->children = array();
        
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
        
        throw new NodeNotFoundException("$name does not exist in " . $this->getPath());
    }
    
    public function attach(\Khameleon\Node $node)
    {
        $this->children[$node->getName()] = $node;
    }
    
    public function detach(\Khameleon\Node $node)
    {
        $name = $node->getName();
        
        if(isset($this->children[$name]))
        {
            unset($this->children[$name]);
        }
    }
    
    public function count()
    {
        return count($this->children);
    }
    
    public function unlink()
    {
        if($this->parent !== null)
        {
            $this->parent->detach($this);
        }
    }
    
    public function remove()
    {
        $this->fileSystem->remove($this->getPath());
    }
}