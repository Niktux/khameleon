<?php

namespace Khameleon\Tests\Khameleon\Memory;

require_once 'AbstractFilterIteratorTestCase.php';

use Khameleon\Iterators\NodeFilterIterator;

class NodeFilterIteratorTest extends AbstractFilterIteratorTestCase
{
    public function testFilter()
    {
        $it = new NodeFilterIterator($this->iterator);
        
        $this->assertCount(2, $it);
        $this->assertContains($this->inFile, $it);
        $this->assertContains($this->inDir, $it);
        $this->assertNotContains($this->outFile, $it);
    }
}