<?php namespace inject;

interface MethodInterception {

  /**
   * Intercept a method invocation
   *
   * @param  inject.MethodInvocation $invocation
   */
  public function invoke($invocation);
}
