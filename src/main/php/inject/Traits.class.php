<?php namespace inject;

use inject\Injector;

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