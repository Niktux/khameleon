<?php

namespace Khameleon\Traits;

trait SearchableTrait
{
    /**
     * @returns \Iterator
     */
    public function search($regex)
    {
        return new \Khameleon\Iterators\RegexIterator($regex, $this->read());
    }
    
}