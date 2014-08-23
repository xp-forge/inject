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
   * @param   string name
   * @return  T
   */
  #[@generic(return= 'T')]
  public function get($name= null);
}
