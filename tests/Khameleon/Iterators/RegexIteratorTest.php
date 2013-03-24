<?php

namespace Khameleon\Tests\Khameleon\Memory;


require_once 'AbstractFilterIteratorTestCase.php';

use Khameleon\Iterators\RegexIterator;

class RegexIteratorTest extends AbstractFilterIteratorTestCase
{
    public function testFilter()
    {
        $it = new RegexIterator('~to~', $this->iterator, RegexIterator::FILTER_ON_PATH);
        
        $this->assertCount(2, $it);
        $this->assertContains($this->inFile, $it);
        $this->assertContains($this->inDir, $it);
        $this->assertNotContains($this->outFile, $it);

        $it = new RegexIterator('~some~', $this->iterator, RegexIterator::FILTER_ON_PATH);
        
        $this->assertCount(1, $it);
        $this->assertNotContains($this->inFile, $it);
        $this->assertContains($this->inDir, $it);
        $this->assertNotContains($this->outFile, $it);

        $it = new RegexIterator('~some~', $this->iterator, RegexIterator::FILTER_ON_NAME);
        
        $this->assertCount(0, $it);
        $this->assertNotContains($this->inFile, $it);
        $this->assertNotContains($this->inDir, $it);
        $this->assertNotContains($this->outFile, $it);

        $it = new RegexIterator('~file~', $this->iterator, RegexIterator::FILTER_ON_NAME);
        
        $this->assertCount(1, $it);
        $this->assertContains($this->inFile, $it);
        $this->assertNotContains($this->inDir, $it);
        $this->assertNotContains($this->outFile, $it);
    }
    
    /**
     * @dataProvider providerTestFsFilter
     */
    public function testFsFilter($regex, $mode, $expectedCount)
    {
        $dir = $this->fs->get('test');
        $it = new RegexIterator($regex, $dir->read(), $mode);
        
        $this->assertCount($expectedCount, $it);
    }
    
    public function providerTestFsFilter()
    {
        return array(
            array('~X$~', RegexIterator::FILTER_ON_NAME, 3),
            array('~ooB~', RegexIterator::FILTER_ON_NAME, 4),
            array('~Ba~', RegexIterator::FILTER_ON_NAME, 4),
            array('~Bar~', RegexIterator::FILTER_ON_NAME, 2),
            array('~Baz~', RegexIterator::FILTER_ON_NAME, 2),
            array('~dir~', RegexIterator::FILTER_ON_NAME, 3),
            array('~tor~', RegexIterator::FILTER_ON_NAME, 1),
            array('~^d~', RegexIterator::FILTER_ON_NAME, 4),
            array('~dir[^X]+X$~', RegexIterator::FILTER_ON_NAME, 2),
            array('~dir[^X]+$~', RegexIterator::FILTER_ON_NAME, 1),
            array('~q~', RegexIterator::FILTER_ON_NAME, 0),
                
            array('~st/~', RegexIterator::FILTER_ON_PATH, 7),
            array('~st/dir~', RegexIterator::FILTER_ON_PATH, 3),
            array('~st/[^X]+X$~', RegexIterator::FILTER_ON_PATH, 3),
            array('~st[^X]+X$~', RegexIterator::FILTER_ON_PATH, 3),
            array('~test~', RegexIterator::FILTER_ON_PATH, 7),
            array('~/~', RegexIterator::FILTER_ON_PATH, 7),
        );
    }
}