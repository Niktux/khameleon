<?php

namespace Khameleon\Memory;

use Khameleon\Exceptions\InvalidPathException;

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
    
    public function createFile($path, $content = null)
    {
        $file = $this->putFile($path);
        $file->write($content);
        
        return $this;
    }
    
    public function createDirectory($path)
    {
        $this->putDirectory($path);
        
        return $this;
    }
    
    public function putFile($path)
    {
        if($this->exists($path))
        {
            throw new AlreadyExistingNodeException($path);
        }
        
        $path = $this->getAbsolutePath($path);
        
        return $this->instantiateFile($path);
    }
    
    public function putDirectory($path)
    {
        if($this->exists($path))
        {
            throw new AlreadyExistingNodeException($path);
        }
        
        $path = $this->getAbsolutePath($path);
        
        return $this->instantiateDirectory($path);
    }
    
    private function instantiateFile($absolutePath)
    {
        $parentPath = dirname($absolutePath);
        $parent = $this->fetchNode($parentPath);
        
        if($parent === null)
        {
            $parent = $this->instantiateDirectory($parentPath);
        }
        
        $file = new File($this, basename($absolutePath), $parent);
        $this->nodes[$absolutePath] = $file;
        
        return $file;
    }
    
    private function instantiateDirectory($absolutePath)
    {
        $parentPath = dirname($absolutePath);
        $parent = $this->fetchNode($parentPath);
        
        if($parent === null)
        {
            $parent = $this->instantiateDirectory($parentPath);
        }
        
        $dir = new Directory($this, basename($absolutePath), $parent);
        $this->nodes[$absolutePath] = $dir;
        
        return $dir;
    }
    
    public function exists($path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        
        return isset($this->nodes[$absolutePath]);
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
    
    public function isPathValid($path, $mustBeRelative = false)
    {
        if(is_string($path))
        {
            $path = rtrim($path, DIRECTORY_SEPARATOR);
            
            if(! empty($path) && preg_match('~^[\w-\s:\.\"' . DIRECTORY_SEPARATOR . ']*$~', $path))
            {
                if(($mustBeRelative && $this->isAbsolute($path)) === false)
                {
                    try
                    {
                        // looking for redundant DIRECTORY_SEPARATOR
                        return (stripos(
                            $this->getAbsolutePath($path),
                            str_repeat(DIRECTORY_SEPARATOR, 2)
                        ) === false);
                    }
                    catch(InvalidMountingPointException $e)
                    {
                        // invalid path
                    }
                }
            }
        }
        
        return false;
    }
}