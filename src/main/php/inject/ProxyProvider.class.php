<?php namespace inject;

use lang\XPClass;

#[@generic(implements= ['var'])]
class ProxyProvider extends \lang\Object implements Provider {
  protected $proxy;
  protected $injector;
  protected $interception;

  /**
   * Creates a new type provider
   *
   * @param  lang.XPClass $type
   * @param  inject.Injector $injector
   * @param  inject.MethodInterception $interception
   */
  public function __construct($type, Injector $injector, MethodInterception $interception) {
    $this->proxy= new Proxy($type instanceof XPClass ? $type : XPClass::forName($type));
    $this->injector= $injector;
    $this->interception= $interception;
  }

  /** @return var */
  public function get() {
    return $this->injector->newInstance($this->proxy->withInterception($this->interception));
  }
}
