<?php namespace inject\unittest\fixture;

class Value extends \lang\Object {
  private $backing;

  /** @param var $initial */
  public function __construct($initial) { $this->backing= $initial; }

  /**
   * Returns whether another value is equal to this
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $this->backing === $cmp->backing;
  }
}