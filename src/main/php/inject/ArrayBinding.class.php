<?php namespace inject;

use lang\IllegalArgumentException;
use lang\XPClass;
use lang\Type;

class ArrayBinding extends \lang\Object implements Binding {
  private static $PROVIDER;
  private $type;
  private $binding= [];

  static function __static() {
    self::$PROVIDER= Type::forName('inject.Provider<?>');
  }

  /**
   * Creates a new instance binding
   *
   * @param  var[] $binding
   * @param  lang.ArrayType $type
   * @throws lang.IllegalArgumentException
   */
  public function __construct($binding, $type) {
    $this->type= $type;
    $component= $this->type->componentType();
    foreach ($binding as $impl) {
      if ($impl instanceof XPClass) {
        $this->binding[]= new ClassBinding($impl, $component);
      } else if (self::$PROVIDER->isInstance($impl) || $impl instanceof Provider) {
        $this->binding[]= new ProviderBinding($impl);
      } else if (is_object($impl)) {
        $this->binding[]= new InstanceBinding($impl, $component);
      } else {
        $this->binding[]= new ClassBinding(XPClass::forName((string)$impl), $component);
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
    //return new TypeProvider($this->class, $injector);
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