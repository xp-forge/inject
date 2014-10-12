<?php namespace inject\aop;

use util\Objects;

class MethodInvocation extends \lang\Object {
  protected $instance;
  protected $routine;
  protected $arguments;

  /**
   * Creates a new routine invocation
   *
   * @param  lang.Generic $instance
   * @param  lang.reflect.Routine $routine
   * @param  var[] $arguments
   */
  public function __construct($instance, $routine, $arguments) {
    $this->instance= $instance;
    $this->routine= $routine;
    $this->arguments= $arguments;
  }

  /** @return lang.Generic */
  public function instance() { return $this->instance; }

  /** @return string */
  public function routine() { return $this->routine; }

  /** @return var[] */
  public function arguments() { return $this->arguments; }

  /** @return var */
  public function proceed() {
    return $this->routine->invoke($this->instance, $this->arguments);
 }

  /**
   * Returns whether a given value is equal to this routine invocation
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return (
      $cmp instanceof self &&
      $this->instance === $cmp->instance &&
      $this->routine->equals($cmp->routine) &&
      Objects::equal($this->arguments, $cmp->arguments)
    );
  }
}
