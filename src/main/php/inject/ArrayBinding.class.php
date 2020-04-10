<?php namespace inject;

use lang\{ArrayType, IllegalArgumentException, Primitive, Type, XPClass};

class ArrayBinding implements Binding {
  private $type;
  private $binding= [];

  /**
   * Creates a new instance binding
   *
   * @param  var[] $binding
   * @param  lang.ArrayType $type
   * @throws lang.IllegalArgumentException
   */
  public function __construct($binding, $type) {
    if (!($type instanceof ArrayType)) {
      throw new IllegalArgumentException('Cannot bind an array to a non-array type');
    }

    $this->type= $type;

    $c= $type->componentType();
    if ($c instanceof Primitive) {
      foreach ($binding as $impl) {
        $this->binding[]= new InstanceBinding($impl, $c);
      }
    } else {
      foreach ($binding as $impl) {
        $this->binding[]= Injector::asBinding($c, $impl);
      }
    }
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
    $r= [];
    foreach ($this->binding as $binding) {
      $r[]= $binding->resolve($injector); 
    }
    return $r;
  }
}