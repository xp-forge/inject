<?php namespace inject\unittest;

use inject\Injector;
use inject\unittest\fixture\{FileSystem, Storage, Value};
use lang\ClassLoader;
use unittest\TestCase;
use util\Currency;

abstract class AnnotationsTest extends TestCase {
  protected $inject;

  /**
   * Sets up test case and binds this test case
   *
   * @return void
   */
  public function setUp() {
    $this->inject= new Injector();
    $this->inject->bind(TestCase::class, $this);
    $this->inject->bind(Storage::class, new FileSystem());
    $this->inject->bind(Currency::class, Currency::$EUR, 'EUR');
    $this->inject->bind('string', 'Test', 'name');
  }

  /**
   * Creates a storage subtype from a given definition
   *
   * @param  [:var] $definition
   * @return inject.unittest.fixture.Storage
   */
  protected function newInstance($definition) {
    return ClassLoader::defineClass('inject.unittest.fixture.'.$this->name, Value::class, [], $definition);
  }
}