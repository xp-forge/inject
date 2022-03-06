<?php namespace inject\unittest;

use inject\Injector;
use inject\unittest\fixture\{FileSystem, Storage, Value};
use lang\ClassLoader;
use unittest\Assert;
use util\Currency;

abstract class AnnotationsTest {
  protected $inject;

  #[Before]
  public function inject() {
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
   * @return lang.XPClass
   */
  protected function newInstance($definition) {
    return ClassLoader::defineClass('inject.unittest.fixture.'.$this->name, Value::class, [], $definition);
  }
}