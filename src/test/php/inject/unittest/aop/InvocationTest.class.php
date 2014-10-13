<?php namespace inject\unittest\aop;

use inject\aop\Invocation;
use unittest\TestCase;

class InvocationTest extends TestCase {

  /**
   * Fixture method
   *
   * @param  int $a
   * @param  int $b
   * @param  int $c
   * @return string
   */
  protected function fixture($a, $b, $c) {
    return implode(',', [$a, $b, $c]);
  }

  /**
   * Returns a method invocation for a given fixture in this class
   *
   * @param  string $name
   * @param  var $args
   * @return inject.aop.Invocation
   */
  protected function invocation($name, $args) {
    return new Invocation($this, $this->getClass()->getMethod($name), $args);
  }

  #[@test]
  public function can_create() {
    $this->invocation('fixture', []);
  }

  #[@test]
  public function instance() {
    $this->assertEquals($this, $this->invocation('fixture', [])->instance());
  }

  #[@test]
  public function routine() {
    $this->assertEquals(
      $this->getClass()->getMethod('fixture'),
      $this->invocation('fixture', [])->routine()
    );
  }

  #[@test]
  public function arguments() {
    $this->assertEquals([1, 2, 3], $this->invocation('fixture', [1, 2, 3])->arguments());
  }

  #[@test]
  public function proceed() {
    $this->assertEquals('1,2,3', $this->invocation('fixture', [1, 2, 3])->proceed());
  }
}