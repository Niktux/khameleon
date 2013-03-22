<?php

namespace Khameleon\Memory;

class FileSystem implements \Khameleon\FileSystem
{
    private
        $rootPath,
        $nodes,
        $root;
    
    public function __construct($rootPath = '/')
    {
        $this->rootPath = $this->sanitizeRootPath($rootPath);
        $this->root = new Directory($this->rootPath, null);
        $this->nodes = array($this->rootPath => $this->root);
    }
    
    private function sanitizeRootPath($path)
    {
        $path = ltrim($path, '.');
        
        return DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR);
    }
    
    public function get($path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        $node = $this->fetchNode($absolutePath);
        
        if($node === null)
        {
            throw new \Khameleon\Exceptions\NodeNotFoundException("$path does not exist");
        }
        
        return $node;
    }
    
    private function getAbsolutePath($path)
    {
        if($this->isAbsolute($path))
        {
            $this->checkMountingPointIsCorrect($path);
            return $path;
        }
        
        return $this->getBasePath() . $path;
    }
    
    private function isAbsolute($path)
    {
        return stripos($path, DIRECTORY_SEPARATOR) === 0;
    }
    
    /**
     * @param string $path
     * @throws \Khameleon\Exceptions\Exception
     */
    private function checkMountingPointIsCorrect($path)
    {
        $rootPath = empty($this->rootPath) ? DIRECTORY_SEPARATOR : $this->rootPath;
        
        if(stripos($path, $rootPath) !== 0)
        {
            throw new \Khameleon\Exceptions\InvalidMountingPointException("$path does not belong to this filesystem ($rootPath)");
        }
    }
    
    private function getBasePath()
    {
        return rtrim($this->rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
    
    private function fetchNode($absolutePath)
    {
        $node = null;
    
        if(isset($this->nodes[$absolutePath]))
        {
            $node = $this->nodes[$absolutePath];
        }
    
        return $node;
    }
    
    public function putFile($path)
    {
        $path = $this->getAbsolutePath($path);
        $node = $this->fetchNode($path);
        
        if(! $node instanceof \Khameleon\File)
        {
            if($node !== null)
            {
                throw new \Khameleon\Exceptions\WrongNodeTypeException($node, "$path already exists and is not a file");
            }
            
            $node = $this->instantiateFile($path);
        }
        
        return $node;
    }
    
    private function instantiateFile($absolutePath)
    {
        $directory = $this->putDirectory(dirname($absolutePath));
        $file = new File(basename($absolutePath), $directory);
        $this->nodes[$absolutePath] = $file;
        
        return $file;
    }
    
    public function putDirectory($path)
    {
        $path = $this->getAbsolutePath($path);
        $node = $this->fetchNode($path);
        
        if(! $node instanceof \Khameleon\Directory)
        {
            if($node !== null)
            {
                throw new \Khameleon\Exceptions\WrongNodeTypeException($node, "$path already exists and is not a directory");
            }
        
            $node = $this->instantiateDirectory($path);
        }
        
        return $node;
    }
    
    private function instantiateDirectory($absolutePath)
    {
        $parent = $this->putDirectory(dirname($absolutePath));
        $dir = new Directory(basename($absolutePath), $parent);
        $this->nodes[$absolutePath] = $dir;
        
        return $dir;
    }
    
    public function exists($path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        
        return isset($this->nodes[$absolutePath]);
    }
    
    public function createFile($path, $content = null)
    {
        if($this->exists($path))
        {
            throw new \Khameleon\Exceptions\AlreadyExistingNodeException($path);
        }
        
        $file = $this->putFile($path);
        $file->write($content);
        
        return $this;
    }
    
    public function createDirectory($path)
    {
        if($this->exists($path))
        {
            throw new \Khameleon\Exceptions\AlreadyExistingNodeException($path);
        }
        
        $this->putDirectory($path);
        
        return $this;
    }
}