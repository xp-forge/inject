<?php namespace inject;

use lang\{IllegalArgumentException, XPClass};

class ClassBinding implements Binding {
  protected $class;

  /**
   * Creates a new instance binding
   *
   * @param  string|lang.XPClass $class
   * @param  lang.XPClass $type
   * @throws lang.IllegalArgumentException
   */
  public function __construct($class, $type= null) {
    $c= $class instanceof XPClass ? $class : XPClass::forName($class);
    if ($type && !$type->isAssignableFrom($c)) {
      throw new IllegalArgumentException($type.' is not assignable from '.$c);
    } else if ($c->isInterface() || $c->getModifiers() & MODIFIER_ABSTRACT) {
      throw new IllegalArgumentException('Cannot bind to non-concrete type '.$type);
    }

    $this->class= $c;
  }

  /**
   * Returns a provider for this binding
   *
   * @param  inject.Injector $injector
   * @return inject.Provider<?>
   */
  public function provider($injector) {
    return new TypeProvider($this->class, $injector);
  }

  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @return var
   */
  public function resolve($injector) {
    return $injector->newInstance($this->class);
  }
}