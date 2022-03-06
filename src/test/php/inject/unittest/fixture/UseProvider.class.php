<?php namespace inject\unittest\fixture;

class UseProvider {
  public $provider;

  /** @param inject.Provider<inject.unittest.fixture.Storage> $provider */
  public function __construct($provider) {
    $this->provider= $provider;
  }
}