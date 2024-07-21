<?php namespace inject\unittest\fixture;

class Service {
  public $uris;

  /** @param inject.Implementations<inject.unittest.fixture.URI> $uris */
  public function __construct($uris) {
    $this->uris= $uris->all();
  }
}