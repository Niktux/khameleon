<?php

namespace Khameleon\Iterators;

class FileFilterIterator extends \FilterIterator
{
    public function accept()
    {
        $item = $this->getInnerIterator()->current();

        return $item instanceof \Khameleon\File;
    }
}