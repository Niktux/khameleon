<?php

namespace Khameleon\Iterators;

class RegexIterator extends \FilterIterator
{
    const
        FILTER_ON_NAME = 1,
        FILTER_ON_PATH = 2;
    
    private
        $regex,
        $mode;
    
    public function __construct($regex, \Iterator $iterator, $mode = self::FILTER_ON_NAME)
    {
        parent::__construct($iterator);
        
        $this->regex = $regex;
        $this->mode = self::FILTER_ON_NAME;
        
        $allowedModes = array(self::FILTER_ON_NAME, self::FILTER_ON_PATH);
        if(in_array($mode, $allowedModes))
        {
            $this->mode = $mode;
        }
    }
    
    public function accept()
    {
        $node = $this->getInnerIterator()->current();
        
        if($node instanceof \Khameleon\Node)
        {
            if($this->mode === self::FILTER_ON_PATH)
            {
                $value = $node->getPath();
            }
            else
            {
                $value = $node->getName();
            }
            
            return preg_match($this->regex, $value);
        }
        
        return false;
    }
}