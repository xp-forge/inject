<?php namespace inject;

/**
 * A provider can be used to perform lazy initialization.
 *
 * ```php
 * $injector->bind($intf, XPClass::forName($impl));
 *
 * $provider= $injector->get("inject.Provider<$intf>");
 * $instance= $provider->get();       // Instantiation happens here
 * ```
 *
 * @see   xp://inject.Injector
 */
#[@generic(self= 'T')]
abstract class Provider extends \lang\Object {
  protected $injector;

  public function __construct() {
    $this->injector= \xp::null();
  }

  /**
   * Bind this provider to a given injector and return it
   *
   * @param  inject.Injector
   * @return self
   */
  public function boundTo($injector) {
    $this->injector= $injector;
    return $this;
  }

  /**
   * Gets an instance of "T"
   *
   * @return  T
   */
  #[@generic(return= 'T')]
  public abstract function get();
}
