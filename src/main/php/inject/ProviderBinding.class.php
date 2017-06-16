<?php namespace inject;

use lang\IllegalArgumentException;

class ProviderBinding implements Binding {
  protected $provider;

  /**
   * Creates a new provider binding
   *
   * @param  inject.Provider $provider
   * @throws lang.IllegalArgumentException
   */
  public function __construct($provider) {
    $this->provider= $provider;
  }

  /**
   * Returns a provider for this binding
   *
   * @param  inject.Injector $injector
   * @param  inject.Provider<?>
   */
  public function provider($injector) {
    return $this->provider;
  }

  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @param  var
   */
  public function resolve($injector) {
    return $this->provider->get();
  }
}