<?php

namespace Khameleon;

interface FileSystem
{
    /**
     * @param string $path
     * @return \Khameleon\Node
     * @throws \Khameleon\Exceptions\Exception
     */
    public function get($path);
    
    /**
     * @param string $path
     * @return \Khameleon\File
     */
    public function putFile($path);
    
    /**
     * @param string $path
     * @return \Khameleon\Directory
     */
    public function putDirectory($path);
    
    /**
     * @param string $path
     * @return boolean
     */
    public function exists($path);
    
    /**
     * Fluid interface
     *
     * @param string $path
     * @param string $content
     * @return \Khameleon\FileSystem
     * @throws \Khameleon\Exceptions\Exception
     */
    public function createFile($path, $content = null);
    
    /**
     * Fluid interface
     *
     * @param string $path
     * @return \Khameleon\FileSystem
     * @throws \Khameleon\Exceptions\Exception
     */
    public function createDirectory($path);
    
    //public function mount($path, Directory $subroot);
  //  public function writeFile($path, $content);
    
}