<?php namespace inject;

use lang\reflection\{Type, Kind};
use lang\{IllegalArgumentException, Reflection};

class ClassBinding implements Binding {
  protected $type;

  /**
   * Creates a new instance binding
   *
   * @param  string|lang.XPClass|lang.reflection.Type $class
   * @param  lang.XPClass $type
   * @throws lang.IllegalArgumentException
   */
  public function __construct($class, $type= null) {
    $this->type= $class instanceof Type ? $class : Reflection::type($class);

    if ($type && !$type->isAssignableFrom($this->type->class())) {
      throw new IllegalArgumentException($type.' is not assignable from '.$this->type->name());
    } else if (Kind::$CLASS !== $this->type->kind() || $this->type->modifiers()->isAbstract()) {
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
    return new TypeProvider($this->type, $injector);
  }

  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @return var
   */
  public function resolve($injector) {
    return $injector->newInstance($this->type);
  }
}