<?php namespace inject\unittest;

use inject\ProvisionException;
use inject\unittest\fixture\{FileSystem, Storage, Value};
use lang\{IllegalArgumentException, Runnable};
use unittest\{Expect, Test, TestCase};
use util\Currency;

class AnnotatedConstructorTest extends AnnotationsTest {

  #[Test]
  public function with_inject_annotation_and_type() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject(["type" => "unittest.TestCase"])] __construct' => function($param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function with_inject_annotation_and_restriction() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(TestCase $param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function optional_bound_parameter() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(TestCase $test= null) { $this->injected= $test; }
    ]));
    $this->assertEquals($this, $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function optional_unbound_parameter() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(TestCase $test, $verify= true) { $this->injected= [$test, $verify]; }
    ]));
    $this->assertEquals([$this, true], $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function with_inject_annotation_and_multiple_parameters() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(TestCase $test, Storage $storage) {
        $this->injected= [$test, $storage];
      }
    ]));
    $this->assertEquals([$this, new FileSystem()], $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function with_inject_parameter_annotations() {
    $this->inject->bind(Value::class, $this->newInstance('{
      public $injected;

      public function __construct(
        #[Inject]
        \unittest\TestCase $test,
        #[Inject(name: "EUR")]
        \util\Currency $cur
      ) {
        $this->injected= [$test, $cur];
      }
    }'));
    $this->assertEquals([$this, Currency::$EUR], $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function with_inject_annotation_and_inject_parameter_annotations() {
    $this->inject->bind(Value::class, $this->newInstance('{
      public $injected;

      #[Inject]
      public function __construct(
        \unittest\TestCase $test,
        \inject\unittest\fixture\Storage $storage,
        #[Inject(name: "name", type: "string")]
        $name
      ) {
        $this->injected= [$test, $storage, $name];
      }
    }'));
    $this->assertEquals([$this, new FileSystem(), 'Test'], $this->inject->get(Value::class)->injected);
  }

  #[Test, Expect(['class' => ProvisionException::class, 'withMessage' => '/Error creating an instance/'])]
  public function injecting_unbound_into_constructor_via_method_annotation() {
    $this->inject->bind(Value::class, $this->newInstance([
      '#[Inject] __construct' => function(Runnable $param) { /* Empty */ }
    ]));
    $this->inject->get(Value::class);
  }

  #[Test, Expect(['class' => ProvisionException::class, 'withMessage' => '/Error creating an instance/'])]
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

  #[Test, Expect(['class' => ProvisionException::class, 'withMessage' => '/Error creating an instance/'])]
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
      '__construct' => function(TestCase $test) { $this->injected= $test; }
    ]));
    $this->assertEquals($this, $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function annotation_is_optional_multiple_parameters() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '__construct' => function(TestCase $test, Storage $storage) {
        $this->injected= [$test, $storage];
      }
    ]));
    $this->assertEquals([$this, new FileSystem()], $this->inject->get(Value::class)->injected);
  }

  #[Test]
  public function name_defaults_to_parameter() {
    $this->inject->bind(Value::class, $this->newInstance([
      'injected' => null,
      '#[Inject] __construct' => function(TestCase $test, Storage $storage, string $name) {
        $this->injected= [$test, $storage, $name];
      }
    ]));
    $this->assertEquals([$this, new FileSystem(), 'Test'], $this->inject->get(Value::class)->injected);
  }
}