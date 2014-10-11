<?php namespace inject\unittest;

use inject\Injector;
use unittest\TestCase;
use util\Currency;
use lang\ClassLoader;

abstract class AnnotationsTest extends TestCase {
  protected $inject;

  /**
   * Sets up test case and binds this test case
   */
  public function setUp() {
    $this->inject= new Injector();
    $this->inject->bind('unittest.TestCase', $this);
    $this->inject->bind('util.Currency', Currency::$EUR, 'EUR');
  }

  /**
   * Creates a storage subtype from a given definition
   *
   * @param  [:var] $definition
   * @return inject.unittest.fixture.Storage
   */
  protected function newStorage($definition) {
    return ClassLoader::defineClass(
      'inject.unittest.fixture.'.$this->name,
      'lang.Object',
      ['inject.unittest.fixture.Storage'],
      $definition
    );
  }
}