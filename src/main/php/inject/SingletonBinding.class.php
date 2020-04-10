<?php namespace inject;

use lang\{IllegalArgumentException, XPClass};

class SingletonBinding implements Binding {
  private $class;
  private $instance= null;

  /**
   * Creates a new singleton binding
   *
   * @param  string|lang.XPClass $class
   * @throws lang.IllegalArgumentException
   */
  public function __construct($class) {
    $c= $class instanceof XPClass ? $class : XPClass::forName($class);
    if ($c->isInterface() || $c->getModifiers() & MODIFIER_ABSTRACT) {
      throw new IllegalArgumentException('Cannot bind to non-concrete type '.$c);
    }

    $this->class= $c;
  }

  /**
   * Returns a provider for this binding
   *
   * @param  inject.Injector $injector
   * @param  inject.Provider<?>
   */
  public function provider($injector) {
    return new ResolvingProvider($this, $injector);
  }

  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @param  var
   */
  public function resolve($injector) {
    return $this->instance ?: $this->instance= $injector->newInstance($this->class);
  }
}