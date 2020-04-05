<?php namespace inject;

#[@generic(['implements' => ['var']])]
class InstanceProvider implements Provider {
  protected $instance= null;

  /** @param var */
  public function __construct($instance= null) { $this->instance= $instance; }

  /** @return var */
  public function get() { return $this->instance; }
}
