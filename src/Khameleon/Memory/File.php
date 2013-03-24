<?php

namespace Khameleon\Memory;

class File extends Node implements \Khameleon\File
{
    private
        $ctime,
        $atime,
        $mtime,
        $content;
    
    public function __construct(FileSystem $fs, $name, Directory $parent)
    {
        parent::__construct($fs, $name, $parent);
        
        $this->content = null;
        
        $this->ctime = time();
        $this->atime = time();
        $this->mtime = time();
    }
    
    public function read()
    {
        $this->updateAccessTime();
        
        return $this->content;
    }
    
    public function write($content)
    {
        $this->content = $content;
        
        $this->updateModificationTime();
        $this->updateAccessTime();
        
        return $this;
    }
    
    public function detachFromParent()
    {
        $this->parent->detach($this);
    }
    
    public function getCreationTime()
    {
        return $this->ctime;
    }
    
    public function getAccessTime()
    {
        return $this->atime;
    }
    
    public function getModificationTime()
    {
        return $this->mtime;
    }
    
    private function updateAccessTime()
    {
        $this->atime = time();
    }
    
    private function updateModificationTime()
    {
        $this->mtime = time();
    }
}