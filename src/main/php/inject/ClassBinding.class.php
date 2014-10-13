<?php namespace inject;

use lang\IllegalArgumentException;

class ClassBinding extends \lang\Object implements Binding {
  protected $type;
  protected $class;

  /**
   * Creates a new instance binding
   *
   * @param  lang.XPClass $type
   * @param  lang.XPClass $class
   * @throws lang.IllegalArgumentException
   */
  public function __construct($type, $class) {
    if (!$type->isAssignableFrom($class)) {
      throw new IllegalArgumentException($class.' is not an instance of '.$type);
    } else if ($class->isInterface() || $class->getModifiers() & MODIFIER_ABSTRACT) {
      throw new IllegalArgumentException('Cannot bind to non-concrete type '.$type);
    }

    $this->type= $type;
    $this->class= $class;
  }

  /**
   * Returns a provider for this binding
   *
   * @param  inject.Injector $injector
   * @param  inject.Provider<?>
   */
  public function provider($injector) {
    return (new TypeProvider($this->class))->boundTo($injector);
  }

  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @param  var
   */
  public function resolve($injector) {
    return $injector->newInstance($this->class);
  }
}