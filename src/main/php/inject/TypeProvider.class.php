<?php namespace inject;

#[@generic(implements= ['var'])]
class TypeProvider extends Provider {
  protected $type;

  /**
   * Creates a new type provider
   *
   * @param  lang.XPClass $type
   */
  public function __construct($type) {
    parent::__construct();
    $this->type= $type;
  }

  /** @return var */
  public function get() { return $this->injector->newInstance($this->type); }
}
