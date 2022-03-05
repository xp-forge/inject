<?php namespace inject;

use lang\Generic;

#[Generic(implements: ['var'])]
class InstanceProvider implements Provider {
  protected $instance= null;

  /** @param var */
  public function __construct($instance= null) { $this->instance= $instance; }

  /** @return var */
  public function get() { return $this->instance; }

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