<?php

namespace Khameleon\Iterators;

class DirectoryFilterIterator extends \FilterIterator
{
    public function accept()
    {
        $item = $this->getInnerIterator()->current();

        return $item instanceof \Khameleon\Directory;
    }
}