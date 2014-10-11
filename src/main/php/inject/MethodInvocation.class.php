<?php namespace inject;

use util\Objects;

class MethodInvocation extends \lang\Object {
  protected $instance;
  protected $method;
  protected $arguments;
  public $proceed;

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
    $this->proceed= false;
  }

  /** @return lang.Generic */
  public function instance() { return $this->instance; }

  /** @return string */
  public function method() { return $this->method; }

  /** @return var[] */
  public function arguments() { return $this->arguments; }

  /** @return void */
  public function proceed() { $this->proceed= true; }

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
