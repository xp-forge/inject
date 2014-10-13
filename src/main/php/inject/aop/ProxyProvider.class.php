<?php namespace inject\aop;

use lang\XPClass;
use inject\Injector;

#[@generic(implements= ['var'])]
class ProxyProvider extends \lang\Object implements \inject\Provider {
  protected $proxy;
  protected $injector;

  /**
   * Creates a new type provider
   *
   * @param  var $type Either an XPClass instance or a string
   * @param  inject.Injector $injector
   * @param  inject.aop.Methods $match
   * @param  inject.aop.MethodInterception $interception
   */
  public function __construct($type, Injector $injector, Methods $match, MethodInterception $interception) {
    $this->proxy= new Proxy(
      $type instanceof XPClass ? $type : XPClass::forName($type),
      $match,
      $interception
    );
    $this->injector= $injector;
  }

  /** @return var */
  public function get() {
    return $this->injector->newInstance($this->proxy->type());
  }
}
