<?php namespace inject;

use ArrayAccess, ReturnTypeWillChange;

/**
 * Base class for named lookups
 *
 * @test  xp://inject.unittest.NamedTest
 */
abstract class Named implements ArrayAccess {

  /**
   * Returns whether this named instance provides a given name
   *
   * @param  string $name
   * @return bool
   */
  public abstract function provides($name);

  /**
   * Returns the binding
   *
   * @param  string $name
   * @return inject.Binding
   */
  public abstract function binding($name);

  /** isset() overloading */
  #[ReturnTypeWillChange]
  public function offsetExists($offset) { return $this->provides($offset); }

  /** =[] overloading */
  #[ReturnTypeWillChange]
  public function offsetGet($offset) { return $this->binding($offset); }

  /** []= overloading */
  #[ReturnTypeWillChange]
  public function offsetSet($offset, $value) { /* Empty */ }

  /** unset() overloading */
  #[ReturnTypeWillChange]
  public function offsetUnset($offset) { /* Empty */ }
}