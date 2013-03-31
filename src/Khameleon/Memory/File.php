<?php

namespace Khameleon\Memory;

use Khameleon\Exceptions\CopyException;

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
    
    public function copyTo($target, $override = false)
    {
        if($this->fileSystem->isPathValid($target) === false)
        {
            throw new CopyException('Invalid target path');
        }
        
        if($this->fileSystem->exists($target))
        {
            if($override == false)
            {
                throw new CopyException("Cannot override existing path ($target)");
            }
            
            $node = $this->fileSystem->get($target);
            
            if(! $node instanceof \Khameleon\File)
            {
                throw new CopyException("Copied file cannot override directory ($target)");
            }
            
            if($node === $this)
            {
                throw new CopyException('Cannot copy to itself');
            }
        }
        else
        {
            $node = $this->fileSystem->putFile($target);
        }
        
        $node->write($this->read());
    }
}