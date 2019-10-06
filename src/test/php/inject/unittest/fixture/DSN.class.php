<?php namespace inject\unittest\fixture;

trait DSN {
  private $dsn;

  #[@inject(name= 'dsn', type= 'string')]
  public function connect($dsn) {
    $this->dsn= $dsn;
  }
}