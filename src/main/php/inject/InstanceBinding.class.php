<?php namespace inject;

use lang\IllegalArgumentException;
use util\Objects;

class InstanceBinding extends \lang\Object implements Binding {
  protected $type;
  protected $instance;

  /**
   * Creates a new instance binding
   *
   * @param  lang.Type $type
   * @param  var $instance
   * @throws lang.IllegalArgumentException
   */
  public function __construct($type, $instance) {
    if (!$type->isInstance($instance)) {
      throw new IllegalArgumentException(Objects::stringOf($instance).' is not an instance of '.$type);
    }
    $this->type= $type;
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