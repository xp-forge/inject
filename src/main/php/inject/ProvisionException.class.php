<?php namespace inject;

use lang\XPException;

/** An error occurred when provisioning the object */
class ProvisionException extends XPException implements Lookup {
 
  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @param  var
   */
  public function resolve($injector) {
    throw $this;
  }
}