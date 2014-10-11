<?php namespace inject;

use util\Objects;

class MethodInvocation extends \lang\Object {
  protected $instance;
  protected $method;
  protected $arguments;

  /**
   * Creates a new method invocation
   *
   * @param  lang.Generic $instance
   * @param  string $method
   * @param  var[] $arguments
   */
  public function __construct($instance, $method, $arguments) {
    $this->instance= $instance;
    $this->method= $method;
    $this->arguments= $arguments;
  }

  /** @return lang.Generic */
  public function instance() { return $this->instance; }

  /** @return string */
  public function method() { return $this->method; }

  /** @return var[] */
  public function arguments() { return $this->arguments; }

  /**
   * Proceed with the call
   *
   * @return var
   */
  public function proceed() {
    return call_user_func_array([$this->instance, $this->method], $this->arguments);
  }

  /**
   * Returns whether a given value is equal to this method invocation
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return (
      $cmp instanceof self &&
      $this->instance === $cmp->instance &&
      $this->method === $cmp->method &&
      Objects::equal($this->arguments, $cmp->arguments)
    );
  }
}
