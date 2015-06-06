<?php namespace inject\unittest;

use inject\Injector;
use unittest\TestCase;
use util\Currency;
use lang\ClassLoader;
use inject\unittest\fixture\FileSystem;

abstract class AnnotationsTest extends TestCase {
  protected $inject;

  /**
   * Sets up test case and binds this test case
   */
  public function setUp() {
    $this->inject= new Injector();
    $this->inject->bind('unittest.TestCase', $this);
    $this->inject->bind('inject.unittest.fixture.Storage', new FileSystem());
    $this->inject->bind('util.Currency', Currency::$EUR, 'EUR');
    $this->inject->bind('string', 'Test', 'name');
  }

  /**
   * Creates a storage subtype from a given definition
   *
   * @param  [:var] $definition
   * @return inject.unittest.fixture.Storage
   */
  protected function newInstance($definition) {
    return ClassLoader::defineClass(
      'inject.unittest.fixture.'.$this->name,
      'inject.unittest.fixture.Value',
      [],
      $definition
    );
  }
}