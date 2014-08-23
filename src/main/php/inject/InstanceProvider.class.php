<?php namespace inject;

#[@generic(implements= ['var'])]
class InstanceProvider extends \lang\Object implements Provider {
  protected $instances= array();

  /**
   * Creates a new instance with an optional initial (named) instance
   *
   * @param   var instance
   * @param   string name
   */
  public function __construct($instance= null, $name= null) {
    if (null !== $instance) {
      $this->instances[$name]= $instance;
    }
  }

  /**
   * Adds an instance
   *
   * @param   var instance
   * @param   string name
   */
  public function add($instance, $name= null) {
    $this->instances[$name]= $instance;
  }
  
  /**
   * Gets an instance of a service
   *
   * @param   string name
   * @return  var
   */
  public function get($name= null) {
    return isset($this->instances[$name]) ? $this->instances[$name] : null;
  }
}
