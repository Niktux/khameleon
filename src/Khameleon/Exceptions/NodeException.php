<?php

namespace Khameleon\Exceptions;

class NodeException extends Exception
{
    public
        $node;
    
    public function __construct(\Khameleon\Node $node, $message = null, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->node = $node;
    }
}
