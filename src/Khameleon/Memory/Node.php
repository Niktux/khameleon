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
        if(is_string($newName)
        && ! empty($newName)
        && stripos($newName, DIRECTORY_SEPARATOR) === false)
        {
            $this->name = $newName;
            $this->fileSystem->updateReference($this);
            
            return true;
        }
        
        throw new InvalidNameException();
    }
}