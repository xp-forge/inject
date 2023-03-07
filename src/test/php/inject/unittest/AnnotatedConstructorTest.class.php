<?php namespace inject\unittest;

use inject\ProvisionException;
use inject\unittest\fixture\{FileSystem, Storage, Value};
use lang\{IllegalArgumentException, Runnable};
use test\{Assert, Expect, Test};
use util\Currency;

class AnnotatedConstructorTest extends AnnotationsTest {

  #[Test]
  public function with_inject_annotation_and_type() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject(["type" => "inject.unittest.AnnotationsTest"])] __construct' => function($param) {
        $this->injected= $param;
      }
    ]));
    Assert::equals($this, $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function with_inject_annotation_and_restriction() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(AnnotationsTest $param) { $this->injected= $param; }
    ]));
    Assert::equals($this, $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function optional_bound_parameter() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(AnnotationsTest $test= null) { $this->injected= $test; }
    ]));
    Assert::equals($this, $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function optional_unbound_parameter() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(AnnotationsTest $test, $verify= true) { $this->injected= [$test, $verify]; }
    ]));
    Assert::equals([$this, true], $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function optional_bound_named_parameter() {
    $this->inject->bind('string', 'Test', 'name');
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(string $name= '') { $this->injected= $name; }
    ]));
    Assert::equals('Test', $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function with_inject_annotation_and_multiple_parameters() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(AnnotationsTest $test, Storage $storage) {
        $this->injected= [$test, $storage];
      }
    ]));
    Assert::equals([$this, new FileSystem()], $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function with_inject_parameter_annotations() {
    $this->inject->bind(Value::class, $this->newInstance('{
      public $injected;

      public function __construct(
        #[Inject]
        \inject\unittest\AnnotationsTest $test,
        #[Inject(name: "EUR")]
        \util\Currency $cur
      ) {
        $this->injected= [$test, $cur];
      }
    }'));
    Assert::equals([$this, Currency::$EUR], $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function with_inject_annotation_and_inject_parameter_annotations() {
    $this->inject->bind(Value::class, $this->newInstance('{
      public $injected;

      #[Inject]
      public function __construct(
        \inject\unittest\AnnotationsTest $test,
        \inject\unittest\fixture\Storage $storage,
        #[Inject(name: "name", type: "string")]
        $name
      ) {
        $this->injected= [$test, $storage, $name];
      }
    }'));
    Assert::equals([$this, new FileSystem(), 'Test'], $this->inject->get(Value::class)->injected);
  }

  #[Test, Expect(class: ProvisionException::class, message: '/No bound value for type lang.Runnable/')]
  public function injecting_unbound_into_constructor_via_method_annotation() {
    $this->inject->bind(Value::class, $this->newInstance([
      '#[Inject] __construct' => function(Runnable $param) { /* Empty */ }
    ]));
    $this->inject->get(Value::class);
  }

  #[Test, Expect(class: ProvisionException::class, message: '/No bound value for type lang.Runnable/')]
  public function injecting_unbound_into_constructor_via_parameter_annotation() {
    $this->inject->bind(Value::class, $this->newInstance('{
      #[Inject]
      public function __construct(
        #[Inject]
        \lang\Runnable $param
      ) { }
    }'));
    $this->inject->get(Value::class);
  }

  #[Test, Expect(class: ProvisionException::class, message: '/Error creating an instance/')]
  public function throwing_an_exception_from_constructor_raises_ProvisionException() {
    $this->inject->bind(Value::class, $this->newInstance([
      '__construct' => function() { throw new IllegalArgumentException('Test'); }
    ]));
    $this->inject->get(Value::class);
  }

  #[Test]
  public function annotation_is_optional_single_parameter() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '__construct' => function(AnnotationsTest $test) { $this->injected= $test; }
    ]));
    Assert::equals($this, $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function annotation_is_optional_multiple_parameters() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '__construct' => function(AnnotationsTest $test, Storage $storage) {
        $this->injected= [$test, $storage];
      }
    ]));
    Assert::equals([$this, new FileSystem()], $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function name_defaults_to_parameter() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(AnnotationsTest $test, Storage $storage, string $name) {
        $this->injected= [$test, $storage, $name];
      }
    ]));
    Assert::equals([$this, new FileSystem(), 'Test'], $this->inject->get(Value::class)->injected);
  }
}