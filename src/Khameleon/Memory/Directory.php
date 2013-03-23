<?php

namespace Khameleon\Memory;

use Khameleon\Exceptions\NodeNotFoundException;

class Directory extends Node implements \Khameleon\Directory
{
    private
        $children;
    
    public function __construct(FileSystem $fs, $name, Directory $parent = null)
    {
        parent::__construct($fs, $name, $parent);

        $this->children = array();
    }
    
    public function read()
    {
        return new \ArrayIterator($this->children);
    }
    
    public function recursiveRead()
    {
        $allChildren = new \AppendIterator();
        $allChildren->append($this->read());
        
        foreach($this->children as $child)
        {
            if($child instanceof Directory)
            {
                $allChildren->append($child->recursiveRead());
            }
        }
        
        return $allChildren;
    }
    
    public function get($name)
    {
        if(isset($this->children[$name]))
        {
            return $this->children[$name];
        }
        
        throw new NodeNotFoundException("$name does not exist in " . $this->getPath());
    }
    
    public function attach(Node $node)
    {
        $this->children[$node->getName()] = $node;
    }
    
    public function detach(Node $node)
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
    
    public function isEmpty()
    {
        return empty($this->children);
    }
    
    public function detachFromParent()
    {
        if($this->parent !== null)
        {
            $this->parent->detach($this);
        }
    }
}