<?php namespace inject;

/**
 * A provider can be used to perform lazy initialization.
 *
 * ```php
 * $injector->bind($intf, XPClass::forName($impl));
 *
 * $provider= $injector->get($intf);
 * $instance= $provider->get();       // Instantiation happens here
 * ```
 *
 * @see   xp://inject.Injector
 */
#[@generic(['self' => 'T'])]
interface Provider {
  
  /**
   * Gets an instance of "T"
   *
   * @return  T
   */
  #[@generic(['return' => 'T'])]
  public function get();
}
