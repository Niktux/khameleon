<?php

namespace Khameleon\Memory;

use Khameleon\Exceptions\RemovalException;
use Khameleon\Exceptions\WrongNodeTypeException;
use Khameleon\Exceptions\AlreadyExistingNodeException;
use Khameleon\Exceptions\NodeNotFoundException;
use Khameleon\Exceptions\InvalidMountingPointException;

class FileSystem implements \Khameleon\FileSystem
{
    private
        $rootPath,
        $nodes,
        $root;
    
    public function __construct($rootPath = '/')
    {
        $this->rootPath = $this->sanitizeRootPath($rootPath);
        $this->root = new Directory($this, $this->rootPath, null);
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
            throw new NodeNotFoundException("$path does not exist");
        }
        
        return $node;
    }
    
    private function getAbsolutePath($path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        
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
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    private function checkMountingPointIsCorrect($path)
    {
        $rootPath = empty($this->rootPath) ? DIRECTORY_SEPARATOR : $this->rootPath;
        
        if(stripos($path, $rootPath) !== 0)
        {
            throw new InvalidMountingPointException("$path does not belong to this filesystem ($rootPath)");
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
                throw new WrongNodeTypeException($node, "$path already exists and is not a file");
            }
            
            $node = $this->instantiateFile($path);
        }
        
        return $node;
    }
    
    private function instantiateFile($absolutePath)
    {
        $directory = $this->putDirectory(dirname($absolutePath));
        $file = new File($this, basename($absolutePath), $directory);
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
                throw new WrongNodeTypeException($node, "$path already exists and is not a directory");
            }
        
            $node = $this->instantiateDirectory($path);
        }
        
        return $node;
    }
    
    private function instantiateDirectory($absolutePath)
    {
        $parent = $this->putDirectory(dirname($absolutePath));
        $dir = new Directory($this, basename($absolutePath), $parent);
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
            throw new AlreadyExistingNodeException($path);
        }
        
        $file = $this->putFile($path);
        $file->write($content);
        
        return $this;
    }
    
    public function createDirectory($path)
    {
        if($this->exists($path))
        {
            throw new AlreadyExistingNodeException($path);
        }
        
        $this->putDirectory($path);
        
        return $this;
    }
    
    public function remove($input)
    {
        $node = $this->convertRemovalInputToNode($input);
        
        if($node instanceof \Khameleon\Directory && $node->isEmpty() !== true)
        {
            throw new RemovalException($node->getPath() . ' is not an empty directory');
        }
        
        $this->unregisterNode($node);
    }
    
    private function convertRemovalInputToNode($removalInput)
    {
        if(! $removalInput instanceof \Khameleon\Node)
        {
            $absolutePath = $this->getAbsolutePath($removalInput);
            $removalInput = $this->fetchNode($absolutePath);
        
            if($removalInput === null)
            {
                throw new NodeNotFoundException("$absolutePath does not exist");
            }
        }
        
        $this->checkRemovalPreconditions($removalInput);
        
        return $removalInput;
    }
    
    private function checkRemovalPreconditions(\Khameleon\Node $node)
    {
        if($node === $this->root)
        {
            throw new RemovalException("Cannot remove root");
        }
    }
    
    private function unregisterNode(\Khameleon\Node $node)
    {
        unset($this->nodes[$node->getPath()]);
        $node->detachFromParent();
    }
    
    public function recursiveRemove($input)
    {
        $node = $this->convertRemovalInputToNode($input);
        
        if($node instanceof Directory)
        {
            foreach($node->read() as $child)
            {
                $child->recursiveRemove();
            }
        }
        
        $this->unregisterNode($node);
    }
}