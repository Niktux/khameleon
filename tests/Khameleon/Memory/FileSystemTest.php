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

    /**
     * @dataProvider providerTestGetRoot
     */
    public function testGetRoot($rootPath)
    {
        $fs = new \Khameleon\Memory\FileSystem($rootPath);
        $root = $fs->get($rootPath);
        
        $this->assertEquals(rtrim($rootPath, DIRECTORY_SEPARATOR), $root->getPath());
    }
    
    public function providerTestGetRoot()
    {
        return array(
            array('/'),
            array(self::ROOT_DIR),
        );
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
     * @expectedException \Khameleon\Exceptions\Exception
     */
    public function testInvalidGet()
    {
        $this->fs->get('not_exist');
    }
    
    public function testCreateFile()
    {
        $path = 'path/to/new/file';
        $this->assertFalse($this->fs->exists($path));
        
        $content = 'This is ZE content';
        $return = $this->fs->createFile($path, $content);
        
        $this->assertTrue(($this->fs->exists($path)));
        
        $file = $this->fs->get($path);
        $this->assertInstanceOf('\Khameleon\File', $file);
        $this->assertSame($content, $file->read());
        $this->assertSame(self::ROOT_DIR . $path, $file->getPath());
        
        // fluid interface
        $this->assertInstanceOf('\Khameleon\FileSystem', $return);
    }
    
    /**
     * @dataProvider providerCreateFileMethod
     * @expectedException \Khameleon\Exceptions\AlreadyExistingNodeException
     */
    public function testCreateFileTwice($createMethod)
    {
        $path = 'path/to/x/file';
        $this->fs->$createMethod($path);
        $this->fs->$createMethod($path);
    }
    
    /**
     * @dataProvider providerCreateFileMethod
     * @expectedException \Khameleon\Exceptions\AlreadyExistingNodeException
     */
    public function testTryToCreateExistingFile($createMethod)
    {
        $path = 'path/to/y/file';
        $this->fs->putFile($path);
        $this->fs->$createMethod($path);
    }
    
    /**
     * @dataProvider providerCreateFileMethod
     * @expectedException \Khameleon\Exceptions\AlreadyExistingNodeException
     */
    public function testTryToCreateFileOverExistingDirectoryPath($createMethod)
    {
        $path = 'path/to/z/dir';
        $this->fs->putDirectory($path);
        $this->fs->$createMethod($path);
    }
        
    public function testCreateDirectory()
    {
        $path = 'path/to/new/dir';
        $this->assertFalse($this->fs->exists($path));
    
        $return = $this->fs->createDirectory($path);
    
        $this->assertTrue(($this->fs->exists($path)));
    
        $dir = $this->fs->get($path);
        $this->assertInstanceOf('\Khameleon\Directory', $dir);
        $this->assertSame(self::ROOT_DIR . $path, $dir->getPath());
    
        // fluid interface
        $this->assertInstanceOf('\Khameleon\FileSystem', $return);
    }
    
    /**
     * @dataProvider providerCreateDirectoryMethod
     * @expectedException \Khameleon\Exceptions\AlreadyExistingNodeException
     */
    public function testTryToCreateDirectoryTwice($createMethod)
    {
        $path = 'path/to/a/dir';
        $this->fs->$createMethod($path);
        $this->fs->$createMethod($path);
    }
    
    /**
     * @dataProvider providerCreateDirectoryMethod
     * @expectedException \Khameleon\Exceptions\AlreadyExistingNodeException
     */
    public function testTryToCreateExistingDirectory($createMethod)
    {
        $path = 'path/to/b/dir';
        $this->fs->createDirectory($path);
        $this->fs->$createMethod($path);
    }
    
    /**
     * @dataProvider providerCreateDirectoryMethod
     * @expectedException \Khameleon\Exceptions\AlreadyExistingNodeException
     */
    public function testTryToCreateDirectoryOverExistingFilePath($createMethod)
    {
        $path = 'path/to/c/file';
        $this->fs->createFile($path);
        $this->fs->$createMethod($path);
    }
    
    public function providerCreateFileMethod()
    {
        return array(
            array('createFile'),
            array('putFile'),
        );
    }
    
    public function providerCreateDirectoryMethod()
    {
        return array(
            array('createDirectory'),
            array('putDirectory'),
        );
    }
    
    public function providerRemoveMethod()
    {
        return array(
            array('remove'),
            array('recursiveRemove'),
        );
    }
    
    public function testRemove()
    {
        $paths = array(
            'file1'  => 'path/to/one/day/file',
            'file2a' => 'path/to/other/file',
            'file2b' => 'path/to/other/fileinsamedir',
            'dir'    => 'path/to/some/empty/dir',
        );
        
        $this->fs
            ->createFile($paths['file1'])
            ->createFile($paths['file2a'])
            ->createFile($paths['file2b'])
            ->createDirectory($paths['dir']);
        
        foreach($paths as $path)
        {
            $this->assertTrue($this->fs->exists($path), "Precondition : $path should exist");
        }
        
        $dir2 = $this->fs->get(dirname($paths['file2a']));
        
        $this->fs->remove($p = $paths['file1']);
        $this->assertFalse($this->fs->exists($p), "$p has not been removed");
        $this->assertTrue($this->fs->exists(dirname($p)), dirname($p) . "should still exist");
        $this->assertTrue($this->fs->exists($paths['file2a']));
        $this->assertTrue($this->fs->exists($paths['file2b']));
        $this->assertTrue($this->fs->exists($paths['dir']));
        $this->assertEquals(2, count($dir2));
        
        $this->fs->remove($p = $paths['file2a']);
        $this->assertFalse($this->fs->exists($p), "$p has not been removed");
        $this->assertTrue($this->fs->exists(dirname($p)), dirname($p) . "should still exist");
        $this->assertTrue($this->fs->exists($paths['file2b']));
        $this->assertTrue($this->fs->exists($paths['dir']));
        $this->assertEquals(1, count($dir2));
        
        $this->fs->remove($p = $paths['dir']);
        $this->assertFalse($this->fs->exists($p), "$p has not been removed");
        $this->assertTrue($this->fs->exists(dirname($p)), dirname($p) . "should still exist");
        $this->assertTrue($this->fs->exists($paths['file2b']));
        $this->assertEquals(1, count($dir2));
        
        $this->fs->remove($p = $paths['file2b']);
        foreach($paths as $path)
        {
            $this->assertFalse($this->fs->exists($path));
        }
        $this->assertEquals(0, count($dir2));
    }
    
    /**
     * @dataProvider providerRemoveMethod
     */
    public function testRemoveAllDirs($removeMethod)
    {
        $fs = new \Khameleon\Memory\FileSystem('/');
        $dir = $fs->putDirectory('one/two/three/four');
        $this->assertEquals(0, count($dir));
        
        $fs->$removeMethod($p = 'one/two/three/four');
        $this->assertFalse($fs->exists($p), "$p has not been removed");
        $this->assertTrue($fs->exists('one/two/three'));
        
        $fs->$removeMethod($p = 'one/two/three');
        $this->assertFalse($fs->exists($p), "$p has not been removed");
        $this->assertTrue($fs->exists('one/two'));
        
        $fs->$removeMethod($p = 'one/two');
        $this->assertFalse($fs->exists($p), "$p has not been removed");
        $this->assertTrue($fs->exists('one'));
        
        $fs->$removeMethod($p = 'one');
        $this->assertFalse($fs->exists($p), "$p has not been removed");
        $this->assertTrue($fs->exists('/'));
    }
    
    /**
     * @expectedException \Khameleon\Exceptions\Exception
     * @dataProvider providerTestTryToRemoveNotEMptyDir
     */
    public function testTryToRemoveNotEMptyDir($createFile, $pathToCreate, $pathToRemove)
    {
        if($createFile === true)
        {
            $this->fs->createFile($pathToCreate);
        }
        else
        {
            $this->fs->createDirectory($pathToCreate);
        }
        
        $this->fs->remove($pathToRemove);
    }
    
    public function providerTestTryToRemoveNotEMptyDir()
    {
        return array(
            array(true, 'path/to/file', 'path/to'),
            array(true, 'path/to/file', self::ROOT_DIR . 'path/to'),
            array(true, 'path/to/file', 'path'),
            array(true, 'path/to/file', self::ROOT_DIR . 'path'),
            array(false, 'path/to/dir', 'path/to'),
            array(false, 'path/to/dir', self::ROOT_DIR . 'path/to'),
            array(false, 'path/to/dir', self::ROOT_DIR),
            array(false, 'path/to/dir', '/'),
        );
    }
    
    /**
     * @dataProvider providerRemoveMethod
     * @expectedException \Khameleon\Exceptions\RemovalException
     */
    public function testCannotRemoveRoot($removeMethod)
    {
       $this->fs->$removeMethod(self::ROOT_DIR);
    }
    
    /**
     * @expectedException \Khameleon\Exceptions\NodeNotFoundException
     */
    public function testTryToRemoveNotExistingFile()
    {
        $this->fs->remove('i/have/never/existed');
    }
    
    /**
     * @dataProvider providerRemoveMethod
     * @expectedException \Khameleon\Exceptions\NodeNotFoundException
     */
    public function testTryToRemoveNotExistingDirectory($removeMethod)
    {
        $this->fs->$removeMethod('i/have/never/existed');
    }

    public function testRecursiveRemoveOnFile()
    {
        $path = 'path/to/new/file';
        $file = $this->fs->putFile($path);
        
        $parent = $this->fs->get(dirname($path));
        
        $this->assertTrue($this->fs->exists($path));
        $this->assertContains($file, $parent->read());
        
        $this->fs->recursiveRemove($path);
        
        $this->assertFalse($this->fs->exists($path));
        $this->assertNotContains($file, $parent->read());
    }
    
    public function testRecursiveRemove()
    {
        $fs = new \Khameleon\Memory\FileSystem('/');
        $fs->createDirectory('one/two/three/four')
           ->createDirectory('one/two/three/five')
           ->createFile('one/two/three/file')
           ->createDirectory('one/two/third');
        
        $this->assertFalse($fs->get('one/two')->isEmpty());
        $this->assertTrue($fs->exists('one/two'));
        $this->assertTrue($fs->exists('one/two/three'));
        $this->assertTrue($fs->exists('one/two/three/four'));
        
        $fs->recursiveRemove('one/two');
        
        $this->assertFalse($fs->exists($p = 'one/two'),            "$p should not exist anymore");
        $this->assertFalse($fs->exists($p = 'one/two/three'),      "$p should not exist anymore");
        $this->assertFalse($fs->exists($p = 'one/two/three/four'), "$p should not exist anymore");
        $this->assertFalse($fs->exists($p = 'one/two/three/five'), "$p should not exist anymore");
        $this->assertFalse($fs->exists($p = 'one/two/three/file'), "$p should not exist anymore");
        $this->assertFalse($fs->exists($p = 'one/two/third'),      "$p should not exist anymore");
        $this->assertTrue($fs->get($p = 'one')->isEmpty(), "$p should be empty");
        $this->assertEmpty(iterator_to_array($fs->get($p = 'one')->recursiveRead()));
    }
    
    /**
     * @dataProvider providerRemoveMethod
     */
    public function testRemoveOnNodes($removeMethod)
    {
        $dir = $this->fs->putDirectory('dir');
        $file = $this->fs->putFile('file');

        $this->fs->$removeMethod($dir);
        $this->assertFalse($this->fs->exists('dir'));
        
        $this->fs->$removeMethod($file);
        $this->assertFalse($this->fs->exists('file'));
    }
    
    /**
     * @dataProvider providerTestRemoveWithInvalidParameters
     * @expectedException Khameleon\Exceptions\NodeNotFoundException
     */
    public function testRemoveWithInvalidParameters($removeMethod, $input)
    {
        $this->fs->$removeMethod(null);
    }
    
    public function providerTestRemoveWithInvalidParameters()
    {
        $inputs = array( null, '', ' ', array(), function (){}, 42, new \StdClass);
        $removeMethods = array('remove', 'recursiveRemove');
        
        $datas = array();
        foreach($removeMethods as $method)
        {
            foreach($inputs as $input)
            {
                $datas[] = array($method, $input);
            }
        }
        
        return $datas;
    }
    
}