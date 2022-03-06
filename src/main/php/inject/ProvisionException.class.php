<?php namespace inject;

use lang\XPException;

/** An error occurred when provisioning the object */
class ProvisionException extends XPException implements Binding {
 
  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @return var
   */
  public function resolve($injector) {
    throw $this;
  }

  /**
   * Returns a provider for this binding
   *
   * @param  inject.Injector $injector
   * @return inject.Provider<?>
   */
  public function provider($injector) {
    throw $this;
  }
}