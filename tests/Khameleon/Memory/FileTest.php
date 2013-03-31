<?php

namespace Khameleon\Tests\Khameleon\Memory;

class FileTest extends \PHPUnit_Framework_TestCase
{
    const
        CONTENT_A = 'aaaaa',
        CONTENT_B = 'bbbb b  bbbb',
        CONTENT_C = "cccc\ncccc\n",
        CONTENT_D = "d";
    
    private
        $fs;

    public function setUp()
    {
        $this->fs = new \Khameleon\Memory\FileSystem('/');
        $this->fs->createFile('copy/destination/file', self::CONTENT_D);
    }
    
    public function testReadWrite()
    {
        $path = 'path/to/my/file';
        
        $this->assertFalse($this->fs->exists($path), "File <$path> should not exist");
        
        $f = $this->fs->putFile($path);
        $this->assertTrue($this->fs->exists($path), "File <$path> should have been created");
        $this->assertEmpty($f->read(), "File <$path> should be empty");
        
        $content = "This the file content\n";
        $return = $f->write($content);
        $this->assertNotEmpty($f->read(), "File <$path> should not be empty anymore");
        $this->assertSame($content, $f->read(), "File <$path> should contain the correct content");
        $this->assertSame($f, $return, 'write() should return $this');
        
        $otherFile = $this->fs->putFile('path/to/other/file');
        $otherFile->write('another content');
        
        $this->assertSame($content, $f->read(), "File <$path> should still contain the same content");
        $this->assertNotSame($content, $otherFile->read(), "Other file should contain another content");
    }
    
    /**
     * @dataProvider providerTestRemove
     */
    public function testRemove($removeMethod)
    {
        $path = 'path/to/my/file';
        
        $file = $this->fs->putFile($path);
        $dir = $this->fs->get(dirname($path));
        
        $nbChildren = count($dir);
        $children = iterator_to_array($dir->read());
        $this->assertContains($file, $children);
        
        $file->$removeMethod();
        
        $this->assertFalse($this->fs->exists($path), "$path should not exist after remove()");
        $this->assertEquals($nbChildren - 1, count($dir));
        $children = iterator_to_array($dir->read());
        $this->assertNotContains($file, $children);
    }
    
    public function providerTestRemove()
    {
        return array(
            array('remove'),
            array('recursiveRemove')
        );
    }
    
    public function testGetTimeMethods()
    {
        $start = time();
        $file = $this->fs->putFile('path/to/file');
        
        $ctime = $file->getCreationTime();
        $mtime = $file->getModificationTime();
        $atime = $file->getAccessTime();
        
        $this->assertGreaterThanOrEqual($start, $ctime, 'ctime #1');
        $this->assertEquals($ctime, $mtime, 'mtime #1');
        $this->assertEquals($ctime, $atime, 'atime #1');
        
        $file->write('some content');
        
        $ctime2 = $file->getCreationTime();
        $mtime2 = $file->getModificationTime();
        $atime2 = $file->getAccessTime();
        
        $this->assertEquals($ctime, $ctime2, 'ctime #2');
        $this->assertGreaterThanOrEqual($mtime, $mtime2, 'mtime #2');
        $this->assertGreaterThanOrEqual($atime, $atime2, 'atime #2');
        
        $file->read();
        
        $ctime3 = $file->getCreationTime();
        $mtime3 = $file->getModificationTime();
        $atime3 = $file->getAccessTime();
        
        $this->assertEquals($ctime, $ctime3, 'ctime #3');
        $this->assertEquals($mtime2, $mtime3, 'mtime #3');
        $this->assertGreaterThanOrEqual($atime2, $atime3, 'atime #3');
        
        sleep(1);
        
        $file->read();
        
        $ctime4 = $file->getCreationTime();
        $mtime4 = $file->getModificationTime();
        $atime4 = $file->getAccessTime();
        
        $this->assertEquals($ctime, $ctime4, 'ctime #4');
        $this->assertEquals($mtime3, $mtime4, 'mtime #4');
        $this->assertGreaterThan($atime3, $atime4, 'atime #4');

        sleep(1);
        
        $file->write('other content');
        
        $ctime5 = $file->getCreationTime();
        $mtime5 = $file->getModificationTime();
        $atime5 = $file->getAccessTime();
        
        $this->assertEquals($ctime, $ctime5, 'ctime #5');
        $this->assertGreaterThan($mtime4, $mtime5, 'mtime #5');
        $this->assertGreaterThan($atime4, $atime5, 'atime #5');
    }
    
    /**
     * @dataProvider providerTestCopy
     */
    public function testCopyTo($targetPath, $override)
    {
        $path = 'path/to/file';
        $file = $this->fs->putFile($path);
        
        $this->assertSame($override, $this->fs->exists($targetPath), "$targetPath existence check failed");
        
        $file->write(self::CONTENT_A)->copyTo($targetPath, $override);
        
        $this->assertTrue($this->fs->exists($path), "$path should still exist");
        $this->assertTrue($this->fs->exists($targetPath), "$targetPath should have been created");
        
        $copy = $this->fs->get($targetPath);
        
        $this->assertSame(self::CONTENT_A, $copy->read());
        
        $absoluteTargetPath = DIRECTORY_SEPARATOR . ltrim($targetPath, DIRECTORY_SEPARATOR);
        $this->assertSame($absoluteTargetPath, $copy->getPath());
        $this->assertSame(basename($absoluteTargetPath), $copy->getName());
        
        $file->write(self::CONTENT_B);
        
        $this->assertSame(self::CONTENT_B, $file->read());
        $this->assertSame(self::CONTENT_A, $copy->read());
        
        $copy->write(self::CONTENT_C);
        
        $this->assertSame(self::CONTENT_B, $file->read());
        $this->assertSame(self::CONTENT_C, $copy->read());
    }
    
    public function providerTestCopy()
    {
        return array(
            array('another/sub/tree/and/filename', false),
            array('path/to/filename', false),
            array('path/file', false),
            array('/path/file', false),
            array('/file', false),
            array('copy/destination/file', true)
        );
    }
    
    /**
     * @dataProvider providerTestCopyToErrorCases
     * @expectedException \Khameleon\Exceptions\CopyException
     */
    public function testCopyToErrorCases($target, $override)
    {
        $this->fs
            ->createFile('path/to/file', self::CONTENT_A)
            ->createFile('path/to/otherfile', self::CONTENT_B);
        
        $this->fs->get('path/to/file')->copyTo($target, $override);
    }
    
    public function providerTestCopyToErrorCases()
    {
        return array(
            array('path/to/otherfile', false), // on existing file without override
            array('path/to', false), // on directory
            array('path/to', true), // on directory
            array('path/to/file', false), // on self
            array('path/to/file', true), // on self
        );
    }
}