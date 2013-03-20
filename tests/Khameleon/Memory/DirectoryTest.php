<?php

namespace Khameleon\Tests\Khameleon\Memory;

class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    private
        $otherFile,
        $files,
        $dir,
        $fs;
    
    public function setUp()
    {
        $this->fs = new \Khameleon\Memory\FileSystem('/');
        
        $this->files = array();
        $this->files[] = $this->fs->file('dir/readme.txt');
        $this->files[] = $this->fs->file('dir/conf.ini');
        $this->files[] = $this->fs->file('dir/content.cache');
        $this->otherFile = $this->fs->file('otherdir/content.cache');
        
        $this->dir = $this->fs->get('dir');
        $this->assertInstanceOf('\Khameleon\Directory', $this->dir);
    }
    
    public function testRead()
    {
        $it = $this->dir->read();
        $this->assertInstanceOf('\Iterator', $it);
        
        $children = iterator_to_array($it);
        foreach($this->files as $file)
        {
            $this->assertContains($file, $children);
        }
        
        $this->assertNotContains($this->otherFile, $children);
    }
    
    public function testGet()
    {
        $f = $this->dir->get('conf.ini');
        $this->assertSame('/dir/conf.ini', $f->getPath());
        $this->assertSame($this->fs->get('dir/conf.ini'), $f);
    }
    
    /**
     * @expectedException \Khameleon\Exceptions\Exception
     */
    public function testInvalidGet()
    {
        $this->dir->get('not_exist');
    }
}