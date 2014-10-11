<?php namespace inject\unittest\fixture;

class FileSystem extends \lang\Object implements Storage {

  public function store($data) {
    return 'Stored "'.$data.'"';
  }
}