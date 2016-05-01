<?php namespace inject\unittest\fixture;

use util\Objects;

class Value implements \lang\Value {
  private $backing;

  /** @param var $initial */
  public function __construct($initial) { $this->backing= $initial; }

  /** @return string */
  public function hashCode() { return 'V@'.Objects::hashOf($this->backing); }

  /** @return string */
  public function toString() { return nameof($this).'('.Objects::stringOf($this->backing).')'; }

  /**
   * Returns whether another value is equal to this
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->backing, $value->backing) : 1;
  }
}