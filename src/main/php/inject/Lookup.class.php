<?php namespace inject;

interface Lookup {

  /**
   * Resolves this lookup and returns the instance
   *
   * @param  inject.Injector $injector
   * @param  var
   */
  public function resolve($injector);

}