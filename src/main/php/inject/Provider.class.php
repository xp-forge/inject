<?php namespace inject;

/**
 * A provider can be used to perform lazy initialization inside the
 * injector's `get()` method.
 *
 * ```php
 * $injector->bind($intf, newinstance('inject.Provider<var>', [], [
 *   'get' => function() { ... }
 * ]));
 *
 * $instance= $injector->get($intf);   // invokes above get() method
 * ```
 *
 * @see   xp://inject.Injector
 */
#[@generic(self= 'T')]
interface Provider {
  
  /**
   * Gets an instance of "T"
   *
   * @return  T
   */
  #[@generic(return= 'T')]
  public function get();
}
