<?php

namespace Khameleon\Iterators;

class RegexIterator extends \FilterIterator
{
    private
        $regex;
    
    public function __construct($regex, Iterator $iterator)
    {
        parent::__construct($iterator);
        
        $this->regex = $regex;
    }
    
    abstract public function accept()
    {
        $node = $this->getInnerIterator()->current();
        
        if($node instanceof \Khameleon\Node)
        {
            return preg_match($regex, $node->getName());
        }
        
        return false;
    }
}