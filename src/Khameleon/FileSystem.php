<?php

namespace Khameleon;

/**
 *
 * @author Niktux
 *
 */
interface FileSystem
{
    /**
     * Retrieve node from file system
     *
     * @param string $path
     * @return \Khameleon\Node
     * @throws \Khameleon\Exceptions\NodeNotFoundException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function get($path);
    
    /**
     * Create and returns a file
     *
     * @param string $path
     * @return \Khameleon\File
     * @throws \Khameleon\Exceptions\AlreadyExistingNodeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function putFile($path);
    
    /**
     * Create and returns a directory
     *
     * @param string $path
     * @return \Khameleon\Directory
     * @throws \Khameleon\Exceptions\AlreadyExistingNodeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function putDirectory($path);
    
    /**
     * Check if node exists
     *
     * @param string $path
     * @return boolean
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function exists($path);
    
    /**
     * Create a file without returning it (fluid interface)
     *
     * @param string $path
     * @param string $content
     * @return \Khameleon\FileSystem
     * @throws \Khameleon\Exceptions\AlreadyExistingNodeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function createFile($path, $content = null);
    
    /**
     * Create a directory without returning it (fluid interface)
     *
     * @param string $path
     * @return \Khameleon\FileSystem
     * @throws \Khameleon\Exceptions\AlreadyExistingNodeException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function createDirectory($path);
    
    /**
     * Remove a node (directories must be empty)
     *
     * @param string|\Khameleon\Node node to remove
     * @throws \Khameleon\Exceptions\RemovalException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function remove($input);
    
    /**
     * Remove recursively a node
     *
     * @param string|\Khameleon\Node node to remove
     * @throws \Khameleon\Exceptions\RemovalException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
    */
    public function recursiveRemove($input);
    
    /**
     * Validates path string
     *
     * @param string $path
     * @param boolean $mustBeRelative
     * @returns boolean
     */
    public function isPathValid($path, $mustBeRelative = false);
    
    /**
     * Rename a node
     *
     * @param string $path
     * @param string $newName path is not allowed (use move instead)
     * @throws \Khameleon\Exceptions\InvalidNameException
     * @throws \Khameleon\Exceptions\NodeNotFoundException
     * @throws \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function rename($path, $newName);
    
    /**
     * Pretty print the whole filesystem
     */
    public function __toString();
}