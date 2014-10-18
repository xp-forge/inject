<?php namespace inject\unittest\aop;

use inject\aop\Methods;
use lang\ClassLoader;
use lang\Primitive;

class MethodsTest extends \unittest\TestCase {
  protected static $fixture;

  #[@beforeClass]
  public static function defineFixture() {
    self::$fixture= ClassLoader::defineClass('inject.unittest.aop.Fixture', 'lang.Object', [], '{
      /** @return void */
      public function a() { }

      /** @return int */
      #[@test, @expect("lang.Error")]
      protected function b() { }

      /** @return lang.Generic */
      #[@test]
      private function c() { }

      /** @return int */
      public function d() { }

      public function ab() { }
    }');
  }

  /**
   * Assertion helper
   *
   * @param  string[] $expected
   * @param  inject.aop.Methods $methods
   * @throws unittest.AssertionFailedError
   */
  protected function assertMethods($expected, $methods) {
    $matched= [];
    foreach (self::$fixture->getDeclaredMethods() as $method) {
      if ($methods->match($method)) $matched[]= $method->getName();
    }
    $this->assertEquals($expected, $matched);
  }

  #[@test]
  public function all_methods() {
    $this->assertMethods(['a', 'b', 'c', 'd', 'ab'], Methods::all());
  }

  #[@test]
  public function annotated_with_test() {
    $this->assertMethods(['b', 'c'], Methods::annotatedWith('test'));
  }

  #[@test]
  public function annotated_with_an_annotation_nothing_is_annotated_with() {
    $this->assertMethods([], Methods::annotatedWith('nothing-is-annotated-with-this'));
  }

  #[@test]
  public function returning_void() {
    $this->assertMethods(['a'], Methods::returning('void'));
  }

  #[@test]
  public function returning_int() {
    $this->assertMethods(['b', 'd'], Methods::returning('int'));
  }

  #[@test]
  public function returning_int_type() {
    $this->assertMethods(['b', 'd'], Methods::returning(Primitive::$INT));
  }

  #[@test]
  public function all_of_returning_int_and_annotated_with_expect() {
    $this->assertMethods(['b'], Methods::allOf([
      Methods::returning('int'),
      Methods::annotatedWith('expect')
    ]));
  }

  #[@test]
  public function any_of_returning_int_and_annotated_with_expect() {
    $this->assertMethods(['b', 'd'], Methods::anyOf([
      Methods::returning('int'),
      Methods::annotatedWith('expect')
    ]));
  }

  #[@test]
  public function named_a() {
    $this->assertMethods(['a'], Methods::named('a'));
  }

  #[@test]
  public function named_beginning_with_a() {
    $this->assertMethods(['a', 'ab'], Methods::named('a*'));
  }

  #[@test]
  public function all_via_named() {
    $this->assertMethods(['a', 'b', 'c', 'd', 'ab'], Methods::named('*'));
  }

  #[@test]
  public function acessible_public() {
    $this->assertMethods(['a', 'd', 'ab'], Methods::accessible(MODIFIER_PUBLIC));
  }

  #[@test]
  public function acessible_protected() {
    $this->assertMethods(['b'], Methods::accessible(MODIFIER_PROTECTED));
  }

  #[@test]
  public function acessible_private() {
    $this->assertMethods(['c'], Methods::accessible(MODIFIER_PRIVATE));
  }

  #[@test]
  public function acessible_private_or_protected() {
    $this->assertMethods(['b', 'c'], Methods::accessible(MODIFIER_PRIVATE | MODIFIER_PROTECTED));
  }
}