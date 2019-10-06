<?php namespace inject;

use inject\Injector;
use lang\reflect\TargetInvocationException;

trait Traits {

  /** Injection point */
  public function __construct(Injector $inject) {
    foreach (typeof($this)->getTraits() as $trait) {
      foreach ($trait->getMethods() as $m) {
        $m->hasAnnotation('inject') && $this->{$m->getName()}(...$inject->args($m));
      }
    }
  }
}