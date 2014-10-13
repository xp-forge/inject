<?php namespace inject\aop;

interface Interception {

  /**
   * Intercept an invocation
   *
   * @param  inject.aop.Invocation $invocation
   */
  public function invoke($invocation);
}
