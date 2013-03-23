<?php

namespace Khameleon\Tests\Khameleon\Memory;

class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    private
        $otherFile,
        $files,
        $deeperFile,
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
        $this->deeperFile = $this->fs->putFile('dir/subdir/file');
        
        $this->fs
            ->createFile('dir/subdir/deeperDir/file1')
            ->createFile('dir/subdir/deeperDir/file2')
            ->createDirectory('some/empty/dir');
        
        $this->dir = $this->fs->get('dir');
        $this->assertInstanceOf('\Khameleon\Directory', $this->dir);
    }
    
    /**
     * @dataProvider providerTestRead
     */
    public function testRead($recursive, $nbExpectedElement)
    {
        if($recursive === true)
        {
            $it = $this->dir->recursiveRead();
        }
        else
        {
            $it = $this->dir->read();
        }
        
        $this->assertInstanceOf('\Iterator', $it);
        $this->assertEquals($nbExpectedElement, iterator_count($it));
        
        $children = iterator_to_array($it);
        foreach($this->files as $file)
        {
            $this->assertContains($file, $children);
        }
        
        if($recursive === true)
        {
            $this->assertContains($this->deeperFile, $children);
        }
        else
        {
            $this->assertNotContains($this->deeperFile, $children);
        }
           
        $this->assertNotContains($this->otherFile, $children);
        $this->assertContains($this->fs->get('dir/subdir'), $children);
    }
    
    public function providerTestRead()
    {
        return array(
            array(true, 8),
            array(false, 4),
        );
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
                
            array('dir', 4),
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
    
    /**
     * @expectedException \Khameleon\Exceptions\RemovalException
     */
    public function testTryToRemoveNotEmptyDirectory()
    {
        $this->dir->remove();
    }
    
    public function testRecursiveRemove()
    {
        $this->dir->recursiveRemove();
        
        $deletedPaths = array(
            'dir',
            'dir/readme.txt',
            'dir/conf.ini',
            'dir/content.cache',
            'dir/subdir',
            'dir/subdir/file',
            'dir/subdir/deeperDir',
            'dir/subdir/deeperDir/file1',
            'dir/subdir/deeperDir/file2',
        );
        
        $existingPaths = array(
            'otherdir/content.cache',
            'some/empty/dir'
        );
        
        foreach($deletedPaths as $path)
        {
            $this->assertFalse($this->fs->exists($path), "$path should not exist anymore");
        }
        
        foreach($existingPaths as $path)
        {
            $this->assertTrue($this->fs->exists($path), "$path should still exist");
        }
    }
    
    /**
     * @dataProvider providerTestEmpty
     */
    public function testEmpty($path, $expected)
    {
        $dir = $this->fs->get($path);
        $this->assertSame($expected, $dir->isEmpty());
    }
    
    public function providerTestEmpty()
    {
        return array(
            array('/', false),
            array('dir', false),
            array('dir/subdir', false),
            array('otherdir', false),
            array('dir/subdir/deeperDir/', false),
                
            array('some/empty/dir', true),
        );
    }
}