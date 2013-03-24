<?php

namespace Khameleon\Tests\Khameleon\Memory;

require_once 'AbstractFilterIteratorTestCase.php';

use Khameleon\Iterators\FileFilterIterator;

class FileFilterIteratorTest extends AbstractFilterIteratorTestCase
{
    protected
        $inFile,
        $inDir,
        $outFile,
        $iterator;
    
    public function testFilter()
    {
        $it = new FileFilterIterator($this->iterator);
        
        $this->assertCount(1, $it);
        $this->assertContains($this->inFile, $it);
        $this->assertNotContains($this->inDir, $it);
        $this->assertNotContains($this->outFile, $it);
    }
    
    public function testFsFilter()
    {
        $dir = $this->fs->get('test');
        $it = new FileFilterIterator($dir->read());
        
        $this->assertCount(4, $it);
    }
}