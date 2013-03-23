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
    public function testRename($newName)
    {
        $oldName = 'file';
        $f = $this->fs->putFile("path/to/$oldName");
        
        $this->assertTrue($this->fs->exists($p = "path/to/$oldName"), "$p should exist");
        $this->assertSame('file', $f->getName());
        $this->assertSame("/path/to/$oldName", $f->getPath());
        
        $f->rename($newName);
        
        if($newName !== $oldName)
        {
            $this->assertFalse($this->fs->exists($p = "path/to/$oldName"), "$p should not exist anymore");
        }
        
        $this->assertTrue($this->fs->exists($p = "path/to/$newName"), "$p should exist");
        $this->assertSame($newName, $f->getName());
        $this->assertSame("/path/to/$newName", $f->getPath());
        
        $this->assertInstanceOf('\Khameleon\File', $this->fs->get("path/to/$newName"));
    }
    
    public function providerTestRename()
    {
        return array(
            array(' '),
            array('   '),
            array('toto'),
            array('name with blanks'),
            array('file'), // same name
            array(' file'),
            array('file '),
            array(' file '),
        );
     }
    
    /**
     * @dataProvider providerTestRenameError
     * @expectedException \Khameleon\Exceptions\InvalidNameException
     */
    public function testRenameError($newName)
    {
        $this->fs->putFile('path/to/file')->rename($newName);
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
        );
    }
}