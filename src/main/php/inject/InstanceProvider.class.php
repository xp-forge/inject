<?php namespace inject;

#[@generic(implements= ['var'])]
class InstanceProvider extends \lang\Object implements Provider {
  protected $instance= null;

  /**
   * Creates a new instance with an instance
   *
   * @param   var instance
   */
  public function __construct($instance= null) {
    $this->instance= $instance;
  }

  /**
   * Gets an instance of a service
   *
   * @return  var
   */
  public function get() {
    return $this->instance;
  }
}
