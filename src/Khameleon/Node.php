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
}