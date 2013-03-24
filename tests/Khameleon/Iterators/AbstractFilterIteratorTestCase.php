<?php

namespace Khameleon\Tests\Khameleon\Memory;

use Khameleon\Memory\FileSystem;

class AbstractFilterIteratorTestCase extends \PHPUnit_Framework_TestCase
{
    protected
        $fs,
        $inFile,
        $inDir,
        $outFile,
        $iterator;
    
    public function setUp()
    {
        $fs = new FileSystem();
        
        $fs->createFile('test/fooBarX')
            ->createDirectory('test/directionX')
            ->createFile('test/mooBar')
            ->createDirectory('test/directoryX')
            ->createFile('test/pooBaz')
            ->createFile('test/dooBaz')
            ->createDirectory('test/directly');
        
        $array = array(
            null, '', new \stdClass,
            $this->inFile = $fs->putFile('path/to/file'),
            -13, 0, 4, 666,
            true, false,
            $this->inDir = $fs->putDirectory('to/some/dir'),
            array(),
            array($this->outFile = $fs->putFile('out/file')),
            function (){},
            new \ArrayIterator(array_pad(array(), 5, 'path/to'))
        );
        
        $this->iterator = new \ArrayIterator($array);
        $this->fs = $fs;
    }
}