<?php namespace inject;

use lang\IllegalArgumentException;
use util\Objects;

class InstanceBinding implements Binding {
  protected $instance;

  /**
   * Creates a new instance binding
   *
   * @param  var $instance
   * @param  lang.Type $type
   * @throws lang.IllegalArgumentException
   */
  public function __construct($instance, $type= null) {
    if ($type && !$type->isInstance($instance)) {
      throw new IllegalArgumentException(Objects::stringOf($instance).' is not an instance of '.$type);
    }
    $this->instance= $instance;
  }

  /**
   * Returns a provider for this binding
   *
   * @param  inject.Injector $injector
   * @param  inject.Provider<?>
   */
  public function provider($injector) {
    return new InstanceProvider($this->instance);
  }

  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @param  var
   */
  public function resolve($injector) {
    return $this->instance;
  }
}