<?php namespace inject\unittest\aop;

use inject\aop\MethodDeclaration;
use unittest\TestCase;

class MethodDeclarationTest extends TestCase {

  /** @return void */
  protected function fixture0() { }

  /** @return void */
  protected function fixture1($arg) { }

  /** @return void */
  protected function fixture($a, self $b, array $c, callable $d, $e= true) { }

  /**
   * Returns a method declaration instance for a given fixture in this class
   *
   * @param  string $name
   * @return inject.aop.MethodDeclaration
   */
  protected function declaration($name) {
    return new MethodDeclaration($this->getClass()->getMethod($name));
  }

  #[@test]
  public function can_create() {
    $this->declaration('fixture');
  }

  #[@test]
  public function name() {
    $this->assertEquals('fixture', $this->declaration('fixture')->name());
  }

  #[@test]
  public function signature_for_method_with_zero_parameters() {
    $this->assertEquals('', $this->declaration('fixture0')->signature());
  }

  #[@test]
  public function signature_for_method_with_one_parameter() {
    $this->assertEquals('$arg', $this->declaration('fixture1')->signature());
  }

  #[@test]
  public function signature() {
    $this->assertEquals(
      '$a, inject\unittest\aop\MethodDeclarationTest $b, array $c, callable $d, $e= true',
      $this->declaration('fixture')->signature()
    );
  }

  #[@test]
  public function arguments_for_method_with_zero_parameters() {
    $this->assertEquals('', $this->declaration('fixture0')->arguments());
  }

  #[@test]
  public function arguments_for_method_with_one_parameter() {
    $this->assertEquals('$arg', $this->declaration('fixture1')->arguments());
  }

  #[@test]
  public function arguments() {
    $this->assertEquals('$a, $b, $c, $d, $e', $this->declaration('fixture')->arguments());
  }

  #[@test]
  public function withBody() {
    $this->assertEquals(
      'function fixture1($arg) { return $arg; }',
      $this->declaration('fixture1')->withBody('return $arg;')
    );
  }

  #[@test]
  public function string_representation() {
    $this->assertEquals(
      'inject.aop.MethodDeclaration<function fixture1($arg)>',
      $this->declaration('fixture1')->toString()
    );
  }
}