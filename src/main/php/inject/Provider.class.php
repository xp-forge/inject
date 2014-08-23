<?php namespace inject;

/**
 * Provider
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
