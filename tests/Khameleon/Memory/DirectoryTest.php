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
        $this->files[] = $this->fs->putFile('dir/readme.txt');
        $this->files[] = $this->fs->putFile('dir/conf.ini');
        $this->files[] = $this->fs->putFile('dir/content.cache');
        $this->otherFile = $this->fs->putFile('otherdir/content.cache');
        
        $this->fs->createDirectory('some/empty/dir');
        
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

    /**
     * @dataProvider providerTestCount
     */
    public function testCount($path, $expected)
    {
        $dir = $this->fs->get($path);
        $this->assertInstanceOf('\Khameleon\Directory', $dir);
        $this->assertEquals($expected, count($dir));
    }
    
    public function providerTestCount()
    {
        return array(
            array('/', 3),
            array('', 3),
                
            array('dir', 3),
            array('otherdir', 1),
                
            array('/some/empty/dir', 0),
            array('some/empty/dir', 0),
            array('some/empty', 1),
            array('some', 1),
        );
    }
    
    public function testRemove()
    {
        $path = 'some/empty/dir';
        $dir = $this->fs->get($path);
        $parentDir = $this->fs->get(dirname($path));
        $nbChildren = count($parentDir);
        $children = iterator_to_array($parentDir->read());
        
        $this->assertTrue($this->fs->exists($path));
        $this->assertContains($dir, $children);
        
        $dir->remove();

        $this->assertFalse($this->fs->exists($path), "$path should not exist after remove()");
        $this->assertEquals($nbChildren - 1, count($parentDir));
        $children = iterator_to_array($parentDir->read());
        $this->assertNotContains($dir, $children);
    }
}