<?php namespace inject\unittest\fixture;

class FileSystem extends \lang\Object implements Storage {
  private $path;

  public function __construct($path= '/') {
    $this->path= $path;
  }

  public function equals($cmp) {
    return $cmp instanceof self && $this->path === $cmp->path;
  }
}