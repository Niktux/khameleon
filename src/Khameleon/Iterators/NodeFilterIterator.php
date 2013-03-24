<?php

namespace Khameleon\Iterators;

class NodeFilterIterator extends \FilterIterator
{
    public function accept()
    {
        $item = $this->getInnerIterator()->current();
        
        return $item instanceof \Khameleon\Node;
    }
}