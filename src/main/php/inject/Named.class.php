<?php namespace inject;

/**
 * Base class for named lookups
 *
 * @test  xp://inject.unittest.NamedInstancesTest
 */
abstract class Named extends \lang\Object implements \ArrayAccess {

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

  public function offsetExists($offset) { return $this->provides($offset); }

  public function offsetGet($offset) { return $this->binding($offset); }

  public function offsetSet($offset, $value) { /* Empty */ }

  public function offsetUnset($offset) { /* Empty */ }
}