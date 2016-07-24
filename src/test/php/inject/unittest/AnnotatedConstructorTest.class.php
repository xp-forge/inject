<?php namespace inject\unittest;

use util\Currency;
use lang\Runnable;
use lang\IllegalArgumentException;
use unittest\TestCase;
use inject\ProvisionException;
use inject\unittest\fixture\FileSystem;
use inject\unittest\fixture\Storage;
use inject\unittest\fixture\Value;

class AnnotatedConstructorTest extends AnnotationsTest {

  #[@test]
  public function with_inject_annotation_and_type() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[@inject(type= "unittest.TestCase")] __construct' => function($param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $this->inject->get(Value::class)->injected);
  }

  #[@test]
  public function with_inject_annotation_and_restriction() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[@inject] __construct' => function(TestCase $param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $this->inject->get(Value::class)->injected);
  }

  #[@test]
  public function optional_bound_parameter() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[@inject] __construct' => function(TestCase $test= null) { $this->injected= $test; }
    ]));
    $this->assertEquals($this, $this->inject->get(Value::class)->injected);
  }

  #[@test]
  public function optional_unbound_parameter() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[@inject] __construct' => function(TestCase $test, $verify= true) { $this->injected= [$test, $verify]; }
    ]));
    $this->assertEquals([$this, true], $this->inject->get(Value::class)->injected);
  }

  #[@test]
  public function with_inject_annotation_and_multiple_parameters() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[@inject] __construct' => function(TestCase $test, Storage $storage) {
        $this->injected= [$test, $storage];
      }
    ]));
    $this->assertEquals([$this, new FileSystem()], $this->inject->get(Value::class)->injected);
  }

  #[@test]
  public function with_inject_parameter_annotations() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[@$test: inject, @$cur: inject(name= "EUR")] __construct' => function(TestCase $test, Currency $cur) {
        $this->injected= [$test, $cur];
      }
    ]));
    $this->assertEquals([$this, Currency::$EUR], $this->inject->get(Value::class)->injected);
  }

  #[@test]
  public function with_inject_annotation_and_inject_parameter_annotations() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[@inject, @$name: inject(name= "name", type= "string")] __construct' => function(TestCase $test, Storage $storage, $name) {
        $this->injected= [$test, $storage, $name];
      }
    ]));
    $this->assertEquals([$this, new FileSystem(), 'Test'], $this->inject->get(Value::class)->injected);
  }

  #[@test, @expect(class= ProvisionException::class, withMessage= '/Error creating an instance/')]
  public function injecting_unbound_into_constructor_via_method_annotation() {
    $this->inject->bind(Value::class, $this->newInstance([
      '#[@inject] __construct' => function(Runnable $param) { /* Empty */ }
    ]));
    $this->inject->get(Value::class);
  }

  #[@test, @expect(class= ProvisionException::class, withMessage= '/Error creating an instance/')]
  public function injecting_unbound_into_constructor_via_parameter_annotation() {
    $this->inject->bind(Value::class, $this->newInstance([
      '#[@$param: inject] __construct' => function(Runnable $param) { /* Empty */ }
    ]));
    $this->inject->get(Value::class);
  }

  #[@test, @expect(class= ProvisionException::class, withMessage= '/Error creating an instance/')]
  public function throwing_an_exception_from_constructor_raises_ProvisionException() {
    $this->inject->bind(Value::class, $this->newInstance([
      '__construct' => function() { throw new IllegalArgumentException('Test'); }
    ]));
    $this->inject->get(Value::class);
  }
}