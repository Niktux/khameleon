<?php

namespace Khameleon;

interface FileSystem
{
    public function get($path);
    public function putFile($path);
    public function putDirectory($path);
    
    //public function mount($path, Directory $subroot);
  //  public function writeFile($path, $content);
    
    public function exists($path);
}