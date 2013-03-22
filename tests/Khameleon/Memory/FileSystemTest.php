<?php

namespace Khameleon\Tests\Khameleon\Memory;

class FileSystemTest extends \PHPUnit_Framework_TestCase
{
    const
        ROOT_DIR = '/root/dir/';
    
    private
        $fs;
    
    public function setUp()
    {
        $this->fs = new \Khameleon\Memory\FileSystem(self::ROOT_DIR);
    }
    
    public function testRootDir()
    {
        $relativePath = 'subdir/file';
        $absolutePath = '/root/dir/subdir/file';
        $fileName = 'file';
        
        $fr = $this->fs->putFile($relativePath);
        $fa = $this->fs->get($absolutePath);
        
        $this->assertInstanceOf('\Khameleon\File', $fr);
        $this->assertSame($fa, $fr);
        $this->assertSame($absolutePath, $fr->getPath());
        $this->assertSame($fileName, $fr->getName());
    }
    
    public function testGet()
    {
        $filename = 'myfile';
        $this->assertFalse($this->fs->exists($filename), "<$filename> must not exist");
        
        $ffile = $this->fs->putFile($filename);
        $this->assertTrue($this->fs->exists($filename), "<$filename> must has been created");
        
        $fget = $this->fs->get($filename);
        $this->assertTrue($this->fs->exists($filename), "<$filename> still must exist");
        
        $this->assertSame($fget, $ffile);
        
        $fget2 = $this->fs->get($filename);
        $this->assertTrue($this->fs->exists($filename), "<$filename> still must exist after second get() call");
        $this->assertSame($ffile, $fget2);
        
        
        $dirname = 'mydir';
        $this->assertFalse($this->fs->exists($dirname));
        
        $ddir = $this->fs->putDirectory($dirname);
        $this->assertTrue($this->fs->exists($dirname));
        
        $dget = $this->fs->get($dirname);
        $this->assertTrue($this->fs->exists($dirname));
        
        $this->assertSame($ddir, $dget);
    }
    
    public function testExists()
    {
        $path = 'path/to/my/file';
        $f = $this->fs->putFile($path);
        
        $parts = explode('/', $path);
        // remove filename
        array_pop($parts);
        
        $path = rtrim(self::ROOT_DIR, '/');
        foreach($parts as $part)
        {
            $path .= '/' . $part;
            $this->assertTrue($this->fs->exists($path), "Directory <$path> must has been indirectly created");
            
            $directory = $this->fs->get($path);
            $this->assertInstanceOf('\Khameleon\Directory', $directory); //, var_export($directory, true));
        }
        
        $this->assertInstanceOf('\Khameleon\File', $f);
    }

    /**
     * @dataProvider providerTestFile
     */
    public function testFile($path, $name)
    {
        $this->assertFalse($this->fs->exists($path), "<$path> should not exist");
        
        $f = $this->fs->putFile($path);
        
        $this->assertInstanceOf('\Khameleon\File', $f);
        $this->assertTrue($this->fs->exists($path), "<$path> should exist");
        $this->assertSame(self::ROOT_DIR . $path, $f->getPath());
        $this->assertSame($name, $f->getName(), "Name should be <$name> and not " . $f->getName());
    }
    
    public function providerTestFile()
    {
        return array(
            array('rootFile', 'rootFile'),
            array('path/to/file', 'file'),
        );
    }

    /**
     * @dataProvider providerTestDirectory
     */
    public function testDirectory($path, $name)
    {
        $this->assertFalse($this->fs->exists($path), "<$path> should not exist");
        
        $d = $this->fs->putDirectory($path);
        
        $this->assertInstanceOf('\Khameleon\Directory', $d);
        $this->assertTrue($this->fs->exists($path), "<$path> should exist");
        $this->assertSame(self::ROOT_DIR . $path, $d->getPath());
        $this->assertSame($name, $d->getName(), "Name should be <$name> and not " . $d->getName());
    }
    
    public function providerTestDirectory()
    {
        return array(
            array('rootSubdir', 'rootSubdir'),
            array('path/to/subdir', 'subdir'),
        );
    }
    
    /**
     * @expectedException \Khameleon\Exceptions\InvalidMountingPointException
     */
    public function testInvalidAbsolutePath()
    {
        $this->fs->get('/this/is/an/absolute/path/outside/mounting/point');
    }
    
    /**
     * @expectedException \Khameleon\Exceptions\NodeNotFoundException
     */
    public function testNotExistingGet()
    {
        $this->fs->get('path/to/nowhere');
    }
    
    /**
     * @dataProvider providerTestSanitizeRootDir
     */
    public function testSanitizeRootDir($rootDir, $expected)
    {
        $fs = new \Khameleon\Memory\FileSystem($rootDir);
        $f = $fs->putFile('file');
        
        $this->assertInstanceOf('\Khameleon\File', $f);
        $this->assertSame($expected, $f->getPath());
    }
    
    public function providerTestSanitizeRootDir()
    {
        return array(
            array('/root',   '/root/file'),
            array('/root/',  '/root/file'),
            array('/root//', '/root/file'),
                
            array('root',  '/root/file'),
            array('root/', '/root/file'),
                
            array('path/to',  '/path/to/file'),
            array('path/to/', '/path/to/file'),
                
            array('',  '/file'),
            array('/', '/file'),
                
            array('.',  '/file'),
            array('./', '/file'),
        );
    }
    
    /**
     * @expectedException \Khameleon\Exceptions\WrongNodeTypeException
     */
    public function testInvalidFile()
    {
        $path = 'subdir';
        $d = $this->fs->putDirectory($path);
        
        $this->assertInstanceOf('\Khameleon\Directory', $d);
        $this->fs->putFile($path);
    }
    
    /**
     * @expectedException \Khameleon\Exceptions\WrongNodeTypeException
     */
    public function testInvalidDirectory()
    {
        $path = 'myfile';
        $f = $this->fs->putFile($path);
    
        $this->assertInstanceOf('\Khameleon\File', $f);
        $this->fs->putDirectory($path);
    }
    
    /**
     * @expectedException \Khameleon\Exceptions\Exception
     */
    public function testInvalidGet()
    {
        $this->fs->get('not_exist');
    }
}