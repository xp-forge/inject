<?php namespace inject\unittest;

use inject\Injector;
use inject\unittest\fixture\{FileSystem, Storage, Value};
use lang\ClassLoader;
use test\{Assert, Before};
use util\Currency;

abstract class AnnotationsTest {
  protected $inject;
  protected $id= 0;

  #[Before]
  public function inject() {
    $this->inject= new Injector();
    $this->inject->bind(AnnotationsTest::class, $this);
    $this->inject->bind(Storage::class, new FileSystem());
    $this->inject->bind(Currency::class, Currency::$EUR, 'EUR');
    $this->inject->bind('string', 'Test', 'name');
  }

  /**
   * Creates a type from a given definition
   *
   * @param  [:var] $definition
   * @return lang.XPClass
   */
  protected function newInstance($definition) {
    return ClassLoader::defineClass(
      'inject.unittest.fixture.AnnotationsTest_'.($this->id++),
      Value::class,
      [],
      $definition
    );
  }
}