<?php

namespace Khameleon;

interface FileSystem
{
    public function get($path);
    public function file($path);
    public function directory($path);
    
    //public function mount($path, Directory $subroot);
  //  public function writeFile($path, $content);
    
    public function exists($path);
}