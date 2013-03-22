<?php

namespace Khameleon;

interface FileSystem
{
    public function get($path);
    public function putFile($path);
    public function putDirectory($path);
    
    public function exists($path);
    
    public function createFile($path, $content);
    public function createDirectory($path);
    
    //public function mount($path, Directory $subroot);
  //  public function writeFile($path, $content);
    
}