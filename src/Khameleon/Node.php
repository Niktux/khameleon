<?php

namespace Khameleon;

interface Node
{
    public function getPath();
    public function getName();
    public function remove();
    public function recursiveRemove();
    
    /**
     * Rename node
     *
     * @param string $newName path is not allowed (use move instead)
     * @throws \Khameleon\Exceptions\InvalidNameException
     */
    public function rename($newName);
    
    /**
     * Copy node and its children to another location
     *
     * @param string|\Khameleon\Node $target target path or target Node
     * @param boolean $override if target already exists and $override is false, a exception will be thrown
     */
    public function copyTo($target, $override = false);
    
    /**
     * @params integer $depth current depth
     * @returns string pretty filetree representation
     */
    public function prettyPrint($depth = 0);
    
    /**
     * @returns Node or null
     */
    public function getParent();
    
    /**
     * @returns integer
     */
    public function getDepth();
}