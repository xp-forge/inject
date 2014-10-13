<?php namespace inject\aop;

use lang\XPClass;
use inject\Injector;

#[@generic(implements= ['var'])]
class ProxyProvider extends \inject\Provider {
  protected $type;
  protected $match;
  protected $interception;
  protected $proxy= null;

  /**
   * Creates a new type provider
   *
   * @param  var $type Either an XPClass instance or a string
   * @param  inject.aop.Methods $match
   * @param  inject.aop.Interception $interception
   */
  public function __construct($type, Methods $match, Interception $interception) {
    parent::__construct();
    $this->type= $type instanceof XPClass ? $type : XPClass::forName($type);
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
