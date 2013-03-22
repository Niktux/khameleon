<?php

namespace Khameleon\Exceptions;

class AlreadyExistingNodeException extends Exception
{
    public function __construct($path)
    {
        parent::__construct("$path already exists");
    }
}