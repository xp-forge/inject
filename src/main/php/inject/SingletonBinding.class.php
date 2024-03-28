<?php namespace inject;

use lang\reflection\{Type, Kind};
use lang\{IllegalArgumentException, Reflection};

class SingletonBinding implements Binding {
  private $type;
  private $instance= null;

  /**
   * Creates a new singleton binding
   *
   * @param  string|lang.XPClass|lang.reflection.Type $class
   * @throws lang.IllegalArgumentException
   */
  public function __construct($class) {
    $this->type= $class instanceof Type ? $class : Reflection::type($class);
    if (Kind::$CLASS !== $this->type->kind() || $this->type->modifiers()->isAbstract()) {
      throw new IllegalArgumentException('Cannot bind to non-concrete type '.$this->type->name());
    }
  }

  /**
   * Returns a provider for this binding
   *
   * @param  inject.Injector $injector
   * @return inject.Provider<?>
   */
  public function provider($injector) {
    return new ResolvingProvider($this, $injector);
  }

  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @return var
   */
  public function resolve($injector) {
    return $this->instance ?: $this->instance= $injector->newInstance($this->type);
  }
}