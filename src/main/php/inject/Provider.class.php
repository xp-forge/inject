<?php namespace inject;

use lang\Generic;

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
#[Generic(self: 'T')]
interface Provider {
  
  /**
   * Gets an instance of "T"
   *
   * @return  T
   */
  #[Generic(return: 'T')]
  public function get();
}