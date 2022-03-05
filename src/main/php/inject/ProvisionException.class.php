<?php namespace inject;

use lang\XPException;

/** An error occurred when provisioning the object */
class ProvisionException extends XPException implements Lookup {
 
  /** @return var */
  public function get() { throw $this; }

  /** @return ?self */
  public function provided() { return null; }
 
}