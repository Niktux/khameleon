<?php

namespace Khameleon\Tests\Khameleon\Memory;

require_once 'AbstractFilterIteratorTestCase.php';

use Khameleon\Iterators\DirectoryFilterIterator;

class DirectoryFilterIteratorTest extends AbstractFilterIteratorTestCase
{
    protected
        $inFile,
        $inDir,
        $outFile,
        $iterator;
    
    public function testFilter()
    {
        $it = new DirectoryFilterIterator($this->iterator);

        $this->assertCount(1, $it);
        $this->assertNotContains($this->inFile, $it);
        $this->assertContains($this->inDir, $it);
        $this->assertNotContains($this->outFile, $it);
    }
    
    public function testFsFilter()
    {
        $dir = $this->fs->get('test');
        $it = new DirectoryFilterIterator($dir->read());
    
        $this->assertCount(3, $it);
    }
}