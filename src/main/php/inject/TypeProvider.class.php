<?php namespace inject;

#[@generic(implements= ['var'])]
class TypeProvider extends \lang\Object implements Provider {
  protected $type;
  protected $injector;

  /**
   * Creates a new type provider
   *
   * @param  lang.XPClass $type
   * @param  inject.Injector $injector
   */
  public function __construct($type, $injector) {
    $this->type= $type;
    $this->injector= $injector;
  }

  /** @return var */
  public function get() { return $this->injector->newInstance($this->type); }
}
