<?php

namespace Khameleon\Memory;

use Khameleon\Exceptions\InvalidNameException;

abstract class Node implements \Khameleon\Node
{
    protected
        $fileSystem,
        $name,
        $parent;
    
    public function __construct(FileSystem $fs, $name, Directory $parent = null)
    {
        $this->fileSystem = $fs;
        $this->name = $name;
        $this->parent = $parent;
        
        if($parent !== null)
        {
            $parent->attach($this);
        }
    }
    
    public function getParent()
    {
        return $this->parent;
    }

    public function getName()
    {
        return $this->name;
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
    
    public function remove()
    {
        $this->fileSystem->remove($this);
    }
    
    public function recursiveRemove()
    {
        $this->fileSystem->recursiveRemove($this);
    }
    
    public function rename($newName)
    {
        if(is_string($newName) && ! empty($newName)
        && stripos($newName, DIRECTORY_SEPARATOR) === false)
        {
            $newPath = dirname($this->getPath()) . DIRECTORY_SEPARATOR . $newName;
            
            if($this->fileSystem->exists($newPath) === false)
            {
                $this->name = $newName;
                $this->fileSystem->updateReference($this);
            
                return true;
            }
        }
        
        throw new InvalidNameException();
    }
    
    public function getDepth()
    {
        if($this->parent === null)
        {
            return 0;
        }
        
        return $this->parent->getDepth() + 1;
    }
    
    public function prettyPrint($depth = 0)
    {
        $line = '';
        
        for($i = 1; $i < $depth; $i++)
        {
            $line .= FileSystem::PRETTY_PRINT_PATTERN_DEPTH;
        }
        
        if($i <= $depth)
        {
            $line .= FileSystem::PRETTY_PRINT_PATTERN_LAST_DEPTH;
        }
        
        return $line . $this->getName();
    }
    
    public function detachFromParent()
    {
        if($this->parent !== null)
        {
            $this->parent->detach($this);
            $this->parent = null;
        }
    }
}