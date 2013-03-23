<?php

namespace Khameleon;

interface FileSystem
{
    /**
     * @param string $path
     * @return \Khameleon\Node
     * @throws \Khameleon\Exceptions\NodeNotFoundException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function get($path);
    
    /**
     * @param string $path
     * @return \Khameleon\File
     * @throws \Khameleon\Exceptions\WrongNodeTypeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function putFile($path);
    
    /**
     * @param string $path
     * @return \Khameleon\Directory
     * @throws \Khameleon\Exceptions\WrongNodeTypeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function putDirectory($path);
    
    /**
     * @param string $path
     * @return boolean
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function exists($path);
    
    /**
     * Fluid interface
     *
     * @param string $path
     * @param string $content
     * @return \Khameleon\FileSystem
     * @throws \Khameleon\Exceptions\AlreadyExistingNodeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function createFile($path, $content = null);
    
    /**
     * Fluid interface
     *
     * @param string $path
     * @return \Khameleon\FileSystem
     * @throws \Khameleon\Exceptions\AlreadyExistingNodeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function createDirectory($path);
    
    /**
     * Remove a file or an empty directory
     *
     * @param string path
     * @throws \Khameleon\Exceptions\RemovalException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function remove($path);
    
    /**
     * Remove recursively a directory
     *
     * @param string path
     * @throws \Khameleon\Exceptions\RemovalException
     * @throws \Khameleon\Exceptions\WrongNodeTypeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
    */
    public function recursiveRemove($path);
    
    //public function mount($path, Directory $subroot);
  //  public function writeFile($path, $content);
    
}