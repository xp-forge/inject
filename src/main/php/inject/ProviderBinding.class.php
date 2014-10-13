<?php namespace inject;

use lang\IllegalArgumentException;

class ProviderBinding extends \lang\Object implements Binding {
  protected $type;
  protected $provider;

  /**
   * Creates a new provider binding
   *
   * @param  lang.XPClass $type
   * @param  inject.Provider $provider
   * @throws lang.IllegalArgumentException
   */
  public function __construct($type, $provider) {
    $this->type= $type;
    $this->provider= $provider;
  }

  /**
   * Returns a provider for this binding
   *
   * @param  inject.Injector $injector
   * @param  inject.Provider<?>
   */
  public function provider($injector) {
    return $this->provider->boundTo($injector);
  }

  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @param  var
   */
  public function resolve($injector) {
    return $this->provider->boundTo($injector)->get();
  }
}