<?php namespace inject;

interface Binding {

  /**
   * Resolves this lookup and returns the instance
   *
   * @param  inject.Injector $injector
   * @return var
   */
  public function resolve($injector);

  /**
   * Returns a provider for this binding
   *
   * @param  inject.Injector $injector
   * @return inject.Provider<?>
   */
  public function provider($injector);

}