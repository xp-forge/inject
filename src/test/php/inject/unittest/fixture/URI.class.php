<?php namespace inject\unittest\fixture;

class URI {
  private $backing;

  /** @param string|inject.unittest.fixture.Creation $arg */
  public function __construct($arg) {
    $this->backing= $arg instanceof Creation ? $arg->create() : (string)$arg;
  }
}