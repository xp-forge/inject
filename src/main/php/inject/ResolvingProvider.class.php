<?php namespace inject;

use lang\Generic;

#[Generic(implements: ['var'])]
class ResolvingProvider implements Provider {
  private $binding, $injector;

  /**
   * Creates a new provider which resolves a binding
   *
   * @param  inject.Binding $binding
   * @param  inject.Injector $injector
   */
  public function __construct($binding, $injector) {
    $this->binding= $binding;
    $this->injector= $injector;
  }

  /** @return var */
  public function get() { return $this->binding->resolve($this->injector); }

  /**
   * Resolves this lookup and returns the instance
   *
   * @param  inject.Injector $injector
   * @param  var
   */
  public function resolve($injector) {
    return $this;
  }
}