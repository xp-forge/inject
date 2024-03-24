<?php namespace inject\unittest;

use inject\unittest\fixture\{FileSystem, Storage};
use inject\{Injector, ProvisionException};
use lang\{ClassLoader, XPException, Runnable};
use test\{Assert, Before, Expect, Test};
use util\Currency;

class NewInstanceTest {
  private $storage;

  /**
   * Creates a unique and new fixture subclass with the given definition
   *
   * @param  [:var] $definition
   * @return inject.unittest.fixture.Fixture
   */
  protected function newFixture($definition) {
    return ClassLoader::defineClass(
      'inject.NewInstanceTest_'.uniqid(),
      'inject.unittest.fixture.Fixture',
      [],
      $definition
    );
  }

  /**
   * Calls Injector::newInstance(), unwrapping ProvisionException's cause
   *
   * @param  inject.Injector $inject
   * @param  string|lang.Type $type
   * @return var
   * @throws lang.Throwable
   */
  private function newInstance(Injector $inject, $type) {
    try {
      return $inject->newInstance($type);
    } catch (ProvisionException $e) {
      throw $e->getCause() ?? $e;
    }
  }

  #[Before]
  public function storage() {
    $this->storage= new FileSystem();
  }

  #[Test]
  public function newInstance_performs_injection() {
    $inject= (new Injector())->bind(Storage::class, $this->storage);
    $fixture= $this->newFixture([
      '#[Inject] __construct' => function(Storage $param) { $this->injected= $param; }
    ]);

    Assert::equals($this->storage, $inject->newInstance($fixture)->injected);
  }

  #[Test]
  public function newInstance_performs_named_injection_using_array_form() {
    $inject= (new Injector())->bind(Storage::class, $this->storage, 'test');
    $fixture= $this->newFixture([
      '#[Inject(name: "test")] __construct' => function(Storage $param) { $this->injected= $param; }
    ]);

    Assert::equals($this->storage, $inject->newInstance($fixture)->injected);
  }

  #[Test]
  public function newInstance_performs_named_injection_using_string_form() {
    $inject= (new Injector())->bind(Storage::class, $this->storage, 'test');
    $fixture= $this->newFixture([
      '#[Inject("test")] __construct' => function(Storage $param) { $this->injected= $param; }
    ]);

    Assert::equals($this->storage, $inject->newInstance($fixture)->injected);
  }

  #[Test]
  public function newInstance_works_without_annotation() {
    $inject= (new Injector())->bind(Storage::class, $this->storage);
    $fixture= $this->newFixture([
      '__construct' => function(Storage $param) { $this->injected= $param; }
    ]);

    Assert::equals($this->storage, $inject->newInstance($fixture)->injected);
  }

  #[Test]
  public function newInstance_also_accepts_arguments() {
    $inject= new Injector();
    $fixture= $this->newFixture([
      '__construct' => function(Storage $param) { $this->injected= $param; }
    ]);

    Assert::equals($this->storage, $inject->newInstance($fixture, ['param' => $this->storage])->injected);
  }

  #[Test]
  public function newInstance_performs_partial_injection_with_required_parameter() {
    $inject= new Injector();
    $inject->bind(Storage::class, $this->storage);
    $fixture= $this->newFixture([
      '#[Inject] __construct' => function(Storage $param, $verify) { $this->injected= [$param, $verify]; }
    ]);

    Assert::equals([$this->storage, true], $inject->newInstance($fixture, ['verify' => true])->injected);
  }

  #[Test]
  public function newInstance_performs_partial_injection_with_optional_parameter() {
    $inject= new Injector();
    $inject->bind(Storage::class, $this->storage);
    $fixture= $this->newFixture([
      '#[Inject] __construct' => function(Storage $param, $verify= true) { $this->injected= [$param, $verify]; }
    ]);

    Assert::equals([$this->storage, true], $inject->newInstance($fixture)->injected);
  }

  #[Test, Expect(class: ProvisionException::class, message: '/Cannot instantiate .+/')]
  public function newInstance_catches_cannot_instantiate_when_creating_class_instances() {
    $this->newInstance(new Injector(), $this->newFixture('{
      #[Inject]
      private function __construct() { }
    }'));
  }

  #[Test, Expect(class: ProvisionException::class, message: '/No bound value for type string named "endpoint"/')]
  public function newInstance_throws_when_value_for_required_parameter_not_found() {
    $this->newInstance(new Injector(), $this->newFixture('{
      #[Inject(type: "string", name: "endpoint")]
      public function __construct($uri) { }
    }'));
  }
}