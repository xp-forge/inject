<?php namespace inject\aop;

interface MethodInterception {

  /**
   * Intercept a method invocation
   *
   * @param  inject.aop.MethodInvocation $invocation
   */
  public function invoke($invocation);
}
