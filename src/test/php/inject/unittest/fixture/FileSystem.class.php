<?php namespace inject\unittest\fixture;

class FileSystem implements Storage {
  private $path;

  public function __construct($path= '/') {
    $this->path= $path;
  }
}