<?php namespace inject\unittest\fixture;

class FileSystem implements Storage {
  private $path, $resolve;

  public function __construct($path= '/', $resolve= false) {
    $this->path= $path;
    $this->resolve= $resolve;
  }
}
