Khameleon
=========

Khameleon is a PHP5 library that provides a filesystem abstraction layer.


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
$file = $fs->file('path/to/myfile');
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
        $file = $fs->file('path/to/myfile');
        $file->write('test data');
        
        $obj = new MyClass($fs);
    }
}

$obj = new MyClass(new Khameleon\Local\FileSystem());
```


Development status
------------------
This project is young : interfaces must be extended (unlink, recursive search, ... etc), other implementations must be done (local, ftp, db, memcache, ...), fluid interface like example below :
```php
$fs = new Khameleon\Memory\FileSystem();
$fs->writeFile('/test/file1', 'content')
   ->writeFile('/test/file2', 'other content')
   ->directory('/test')
   ->writeFile('file3', 'still different content');

```

Quality
-------
This project is a TDD project.
