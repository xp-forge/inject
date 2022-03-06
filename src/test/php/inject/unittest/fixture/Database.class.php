<?php namespace inject\unittest\fixture;

class Database implements Storage {
  private $dsns;

  public function __construct(array $dsns) {
    $this->dsns= $dsns;
  }
}