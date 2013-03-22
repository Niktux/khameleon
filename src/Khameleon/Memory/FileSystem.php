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
            throw new \Khameleon\Exceptions\Exception("$path does not belong to this filesystem ($rootPath)");
        }
    }

    public function get($path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        $node = $this->fetchNode($absolutePath);
        
        if($node === null)
        {
            throw new \Khameleon\Exceptions\Exception("$path does not exist");
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
                throw new \Khameleon\Exceptions\Exception("$path already exists and is not a file");
            }
            
            $node = $this->createFile($path);
        }
        
        return $node;
    }
    
    private function createFile($absolutePath)
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
                throw new \Khameleon\Exceptions\Exception("$path already exists and is not a directory");
            }
        
            $node = $this->createDirectory($path);
        }
        
        return $node;
    }
    
    private function createDirectory($absolutePath)
    {
        $parent = $this->putDirectory(dirname($absolutePath));
        $dir = new Directory(basename($absolutePath), $parent);
        $this->nodes[$absolutePath] = $dir;
        
        return $dir;
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
    
    public function exists($path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        
        return isset($this->nodes[$absolutePath]);
    }
    
    private function getBasePath()
    {
        return rtrim($this->rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}