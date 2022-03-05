<?php namespace inject;

use lang\Generic;

/**
 * A provider can be used to perform lazy initialization.
 *
 * ```php
 * $injector->bind(Closeable::class, XPClass::forName($impl));
 *
 * $provider= $injector->get('inject.Provider<lang.Closeable>');
 * $instance= $provider->get();       // Instantiation happens here
 * ```
 *
 * @see   xp://inject.Injector
 */
#[Generic(self: 'T')]
interface Provider extends Lookup {
  
  /**
   * Gets an instance of "T"
   *
   * @return  T
   */
  #[Generic(return: 'T')]
  public function get();
}