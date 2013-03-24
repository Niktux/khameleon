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