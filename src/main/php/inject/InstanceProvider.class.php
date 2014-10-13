<?php namespace inject;

#[@generic(implements= ['var'])]
class InstanceProvider extends Provider {
  protected $instance= null;

  /** @param var */
  public function __construct($instance= null) {
    parent::__construct();
    $this->instance= $instance;
  }

  /** @return var */
  public function get() { return $this->instance; }
}
