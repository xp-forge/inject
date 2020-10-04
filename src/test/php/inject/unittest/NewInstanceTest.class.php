<?php namespace inject\unittest;

use inject\unittest\fixture\Storage;
use inject\{Injector, ProvisionException};
use lang\{ClassLoader, IllegalAccessException, Runnable};
use unittest\{Expect, Inject, Test, TestCase};
use util\Currency;

class NewInstanceTest extends TestCase {

  /**
   * Creates a storage subtype from a given definition
   *
   * @param  [:var] $definition
   * @return inject.unittest.fixture.Storage
   */
  protected function newStorage($definition) {
    return ClassLoader::defineClass(
      'inject.unittest.fixture.'.$this->name,
      'inject.unittest.fixture.Fixture',
      [Storage::class],
      $definition
    );
  }

  /**
   * Calls Injector::newInstance(), unwrapping ProvisionException's cause
   *
   * @param  inject.Injector $inject
   * @param  var $type
   * @return var
   * @throws lang.Throwable
   */
  private function newInstance(Injector $inject, $type) {
    try {
      return $inject->newInstance($type);
    } catch (ProvisionException $e) {
      throw $e->getCause();
    }
  }

  #[Test]
  public function newInstance_performs_injection() {
    $inject= new Injector();
    $inject->bind(TestCase::class, $this);
    $storage= $this->newStorage([
      'injected' => null,
      '#[Inject] __construct' => function(TestCase $param) { $this->injected= $param; }
    ]);
    $this->assertEquals($this, $inject->newInstance($storage)->injected);
  }

  #[Test]
  public function newInstance_performs_named_injection_using_array_form() {
    $inject= new Injector();
    $inject->bind(TestCase::class, $this, 'test');
    $storage= $this->newStorage([
      'injected' => null,
      '#[Inject(["name" => "test"])] __construct' => function(TestCase $param) { $this->injected= $param; }
    ]);
    $this->assertEquals($this, $inject->newInstance($storage)->injected);
  }

  #[Test]
  public function newInstance_performs_named_injection_using_string_form() {
    $inject= new Injector();
    $inject->bind(TestCase::class, $this, 'test');
    $storage= $this->newStorage([
      'injected' => null,
      '#[Inject("test")] __construct' => function(TestCase $param) { $this->injected= $param; }
    ]);
    $this->assertEquals($this, $inject->newInstance($storage)->injected);
  }

  #[Test]
  public function newInstance_also_accepts_arguments() {
    $inject= new Injector();
    $storage= $this->newStorage([
      'injected' => null,
      '__construct' => function(TestCase $param) { $this->injected= $param; }
    ]);
    $this->assertEquals($this, $inject->newInstance($storage, ['param' => $this])->injected);
  }

  #[Test]
  public function newInstance_performs_partial_injection_with_required_parameter() {
    $inject= new Injector();
    $inject->bind(TestCase::class, $this);
    $storage= $this->newStorage([
      'injected' => null,
      '#[Inject] __construct' => function(TestCase $param, $verify) { $this->injected= [$param, $verify]; }
    ]);
    $this->assertEquals([$this, true], $inject->newInstance($storage, ['verify' => true])->injected);
  }

  #[Test]
  public function newInstance_performs_partial_injection_with_optional_parameter() {
    $inject= new Injector();
    $inject->bind(TestCase::class, $this);
    $storage= $this->newStorage([
      'injected' => null,
      '#[Inject] __construct' => function(TestCase $param, $verify= true) { $this->injected= [$param, $verify]; }
    ]);
    $this->assertEquals([$this, true], $inject->newInstance($storage)->injected);
  }

  #[Test, Expect(['class'       => IllegalAccessException::class, 'withMessage' => '/Cannot invoke private constructor/',])]
  public function newInstance_catches_iae_when_creating_class_instances() {
    $inject= new Injector();
    $storage= $this->newStorage('{
      #[Inject]
      private function __construct() { }
    }');
    $this->newInstance($inject, $storage);
  }

  #[Test, Expect(['class'       => ProvisionException::class, 'withMessage' => '/No bound value for type string named "endpoint"/',])]
  public function newInstance_throws_when_value_for_required_parameter_not_found() {
    $inject= new Injector();
    $storage= $this->newStorage([
      '#[Inject(["type" => "string", "name" => "endpoint"])] __construct' => function($param) { }
    ]);
    $this->newInstance($inject, $storage);
  }
}