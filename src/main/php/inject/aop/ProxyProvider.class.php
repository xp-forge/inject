<?php namespace inject\aop;

use lang\XPClass;
use inject\Injector;

#[@generic(implements= ['var'])]
class ProxyProvider extends \lang\Object implements \inject\Provider {
  protected $type;
  protected $injector;
  protected $match;
  protected $interception;
  protected $proxy= null;

  /**
   * Creates a new type provider
   *
   * @param  var $type Either an XPClass instance or a string
   * @param  inject.Injector $injector
   * @param  inject.aop.Methods $match
   * @param  inject.aop.MethodInterception $interception
   */
  public function __construct($type, Injector $injector, Methods $match, MethodInterception $interception) {
    $this->type= $type instanceof XPClass ? $type : XPClass::forName($type);
    $this->injector= $injector;
    $this->match= $match;
    $this->interception= $interception;
  }

  /** @return var */
  public function get() {
    if (null === $this->proxy) {
      $this->proxy= new Proxy($this->type, $this->match, $this->interception);
    }
    return $this->injector->newInstance($this->proxy->type());
  }
}
