<?php namespace inject;

#[@generic(implements= ['var'])]
class InstanceProvider extends \lang\Object implements Provider {
  protected $instance= null;

  /** @param var */
  public function __construct($instance= null) { $this->instance= $instance; }

  /** @retun var */
  public function get() { return $this->instance; }
}
