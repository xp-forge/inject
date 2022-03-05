<?php namespace inject\unittest\fixture;

class Endpoint {
  public $uri;

  /** @param string|inject.unittest.fixture.URI $uri */
  public function __construct($uri) {
    $this->uri= $uri;
  }
}
