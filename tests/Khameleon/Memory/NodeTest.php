<?php

namespace Khameleon\Tests\Khameleon\Memory;

class NodeTest extends \PHPUnit_Framework_TestCase
{
    private
        $fs;

    public function setUp()
    {
        $this->fs = new \Khameleon\Memory\FileSystem('/');
    }
    
    /**
     * @dataProvider providerTestGetPath
     */
    public function testGetPath($path, $expected)
    {
        $this->fs->createFile('path/to/some/file');
        
        $node = $this->fs->get($path);

        $this->assertSame($expected, $node->getPath());
    }
    
    public function providerTestGetPath()
    {
        return array(
            array($p = '/path/to/some/file', $p),
            array($p = '/path/to/some', $p),
            array($p = '/path/to', $p),
            array($p = '/path', $p),
            array($p = '/', ''),
        );
    }
    
    /**
     * @dataProvider providerTestGetParent
     */
    public function testGetParent($path, $expectedParentPath)
    {
        $node = $this->fs
            ->createFile('one/two/three/file')
            ->get($path);
        
        $parent = $node->getParent();
        $parentPath = null;
        
        if($parent instanceof \Khameleon\Node)
        {
            $parentFromPath = $this->fs->get(dirname($path));
            $parentPath = $parent->getPath();
            $this->assertSame($parentFromPath, $parent);
        }
        
        $this->assertSame($expectedParentPath, $parentPath);
    }
    
    public function providerTestGetParent()
    {
        return array(
            array('one/two/three/file', '/one/two/three'),
            array('one/two/three', '/one/two'),
            array('one/two', '/one'),
            array('/one', ''),
            array('/', null),
        );
    }
    
    /**
     * @dataProvider providerTestGetDepth
     */
    public function testGetDepth($path, $expectedDepth)
    {
        $node = $this->fs
            ->createFile('one/two/three/file')
            ->get($path);
        
        $this->assertSame($expectedDepth, $node->getDepth());
    }
    
    public function providerTestGetDepth()
    {
        return array(
            array('one/two/three/file', 4),
            array('one/two/three', 3),
            array('one/two', 2),
            array('/one', 1),
            array('/', 0),
        );
    }
    
    /**
     * @dataProvider providerTestRename
     */
    public function testRename($newName, $useFileSystemObject)
    {
        $oldName = 'file';
        $oldPath = "path/to/$oldName";
        
        $f = $this->fs->putFile($oldPath);
    
        $this->assertTrue($this->fs->exists($oldPath), "$oldPath should exist");
        $this->assertSame('file', $f->getName());
        $this->assertSame("/path/to/$oldName", $f->getPath());
    
        if($useFileSystemObject === true)
        {
            $this->fs->rename($oldPath, $newName);
        }
        else
        {
            $f->rename($newName);
        }
    
        if($newName !== $oldName)
        {
            $this->assertFalse($this->fs->exists($oldPath), "$oldPath should not exist anymore");
        }
    
        $this->assertTrue($this->fs->exists($p = "path/to/$newName"), "$p should exist");
        $this->assertSame($newName, $f->getName());
        $this->assertSame("/path/to/$newName", $f->getPath());
    
        $this->assertInstanceOf('\Khameleon\File', $this->fs->get("path/to/$newName"));
    }
    
    public function providerTestRename()
    {
        $names = array(
            ' ',
            '   ',
            'toto',
            'name with blanks',
            ' file',
            'file ',
            ' file ',
        );
        
        $cases = array();
        foreach($names as $name)
        {
            $cases[] = array($name, true);
            $cases[] = array($name, false);
        }
        
        return $cases;
     }
    
    /**
     * @dataProvider providerTestRenameError
     * @expectedException \Khameleon\Exceptions\Exception
     */
    public function testRenameError($newName)
    {
        $this->fs->createFile('path/to/alreadyExisting');
        $this->fs->putFile('path/to/file')->rename($newName);
    }
    
    /**
     * @dataProvider providerTestRenameError
     * @expectedException \Khameleon\Exceptions\Exception
     */
    public function testRenameUsingFileSystemError($newName)
    {
        $this->fs->createFile('path/to/alreadyExisting');
        
        $path = 'path/to/file';
        $this->fs->createFile($path)->rename($path, $newName);
    }
      
    public function providerTestRenameError()
    {
        return array(
            array(''),
            array(null),
            array('path/to'),
            array('some/dir/path'),
            array('file/'),
            array('toto/'),
            array('/'),
            array('/toto'),
            array(array()),
            array(new \StdClass),
                
            array('alreadyExisting'),
            array('file'),
        );
    }
    
    public function testRenameNameHasNotChangedIfError()
    {
        $path = 'path/to/';
        $oldName ='old';
        $newName = 'new';
        
        $file = $this->fs->createFile($path . $newName)->putFile($path . $oldName);
        $errorTriggered = false;
        
        try
        {
            $file->rename($newName);
        }
        catch(\Khameleon\Exceptions\Exception $e)
        {
            $errorTriggered = true;
        }
        
        $this->assertTrue($errorTriggered, 'An error must be occured');
        $this->assertSame($oldName, $file->getName(), 'Name must not have been changed');
        $this->assertSame('/path/to/old', $file->getPath(), 'Path must not have been changed');
    }
}