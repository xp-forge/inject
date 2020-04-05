<?php namespace inject;

#[@generic(['implements' => ['var']])]
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
}
