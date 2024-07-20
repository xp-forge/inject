<?php namespace inject;

use lang\Generic;

/** @test inject.unittest.ImplementationsTest */
#[Generic(self: 'T')]
class Implementations {
  private $inject, $bindings;

  /**
   * Creates a new instance
   *
   * @param inject.Injector $inject
   * @param [:inject.Binding] $bindings
   */
  public function __construct(Injector $inject, array $bindings) {
    $this->inject= $inject;
    $this->bindings= $bindings;
  }

  /** Returns the default implementation */
  #[Generic(return: 'T')]
  public function default() {
    return current($this->bindings)->resolve($this->inject);
  }

  /** Returns all implementations */
  #[Generic(return: '[:T]')]
  public function all() {
    $r= [];
    foreach ($this->bindings as $name => $binding) {
      $r[$name]= $binding->resolve($this->inject);
    }
    return $r;
  }

  /**
   * Returns the implementation for a given name
   * 
   * @param  string $name
   * @return T
   * @throws inject.ProvisionException if there is no such implementation
   */
  #[Generic(return: 'T')]
  public function named(string $name) {
    if ($binding= $this->bindings[$name] ?? null) {
      return $binding->resolve($this->inject);
    }

    throw new ProvisionException('No implementation named "'.$name.'"');
  }
}