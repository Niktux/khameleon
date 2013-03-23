Khameleon [![Build Status](https://travis-ci.org/Niktux/khameleon.png?branch=master)](https://travis-ci.org/Niktux/khameleon)
=========

Khameleon is a filesystem abstraction layer written in PHP 5.3.


Aim
---
Break filesystem dependency in order to make code more testable. Using Khameleon filesystem and respecting dependency injection principles allow to achieve this goal. Khameleon will provide many implementations of its API. The first ones will be Local FS and In Memory FS (for unit tests).


Getting started
---------------

### Setup your filesystem

```php
<?php

class MyClass
{
    public function __construct(Khameleon\FileSystem $fs)
    {
        // ...
    }
}

$obj = new MyClass(new Khameleon\Local\FileSystem());
```

### Using filesystem

```php
<?php

$fs = new Khameleon\Local\FileSystem();
$file = $fs->putFile('path/to/myfile');
$content = $file->read();

$dir = $fs->get('path/to');
foreach($dir->read() as $node)
{
    echo $node->getPath() . "\n";
}

if($fs->exists('path/to/other/file'))
{
    // ...
}

```

### Test your code
```php
<?php

class MyClassTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $fs = new Khameleon\Memory\FileSystem();
        $fs->createFile('/test/file1', 'content')
           ->createFile('/test/file2', 'other test content')
           ->createDirectory('/other/dir')
           ->createFile('file3', 'still different content');        
           
        $obj = new MyClass($fs);
    }
}
```


Development status
------------------
This project is young : interfaces must be extended, other implementations must be done (local, ftp, db, memcache, ...), ... etc

Quality
-------
This project is a TDD project.
