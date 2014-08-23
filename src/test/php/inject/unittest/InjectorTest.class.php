<?php namespace inject\unittest;

use inject\Injector;
use inject\InstanceProvider;
use lang\XPClass;
use lang\ClassLoader;
use inject\unittest\fixture\FileSystem;

class InjectorTest extends \unittest\TestCase {

  /** @return var[][] */
  protected function bindings() {
    $instance= new FileSystem();
    $provider= new InstanceProvider($instance);
    return [
      ['inject.unittest.fixture.Storage', XPClass::forName('inject.unittest.fixture.FileSystem')],
      ['inject.unittest.fixture.Storage', $instance],
      ['inject.unittest.fixture.Storage', $provider],
      [XPClass::forName('inject.unittest.fixture.Storage'), XPClass::forName('inject.unittest.fixture.FileSystem')],
      [XPClass::forName('inject.unittest.fixture.Storage'), $instance],
      [XPClass::forName('inject.unittest.fixture.Storage'), $provider]
    ];
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

  #[@test]
  public function can_create() {
    new Injector();
  }

  #[@test, @values('bindings')]
  public function bind_interface_to_implementation($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl);
  }

  #[@test, @values('bindings')]
  public function bind_returns_injector_instance($type, $impl) {
    $inject= new Injector();
    $this->assertEquals($inject, $inject->bind($type, $impl));
  }

  #[@test]
  public function get_unbound_type_returns_null() {
    $this->assertNull((new Injector())->get('inject.unittest.fixture.Storage'));
  }

  #[@test, @values('bindings')]
  public function get_implementation_bound_to_interface($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl);
    $this->assertInstanceOf('inject.unittest.fixture.FileSystem', $inject->get($type));
  }

  #[@test, @values('bindings')]
  public function get_named_implementation_bound_to_interface($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl, 'test');
    $this->assertInstanceOf('inject.unittest.fixture.FileSystem', $inject->get($type, 'test'));
  }

  #[@test, @values('bindings')]
  public function get_unbound_named_type_returns_null($type, $impl) {
    $inject= new Injector();
    $this->assertNull($inject->get($type, 'any-name-really'));
  }

  #[@test, @values('bindings')]
  public function get_type_bound_by_different_name_returns_null($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl, 'test');
    $this->assertNull($inject->get($type, 'another-name-than-the-one-bound'));
  }

  #[@test]
  public function constructor_with_inject_annotation_and_type() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject(type= "unittest.TestCase")] __construct' => function($param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function constructor_with_inject_annotation_and_restriction() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject] __construct' => function(\unittest\TestCase $param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Unknown injection type/')]
  public function constructor_injecting_unbound() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject] __construct' => function(\unittest\TestCase $param) { /* Empty */ }
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Test/')]
  public function constructor_throwing_an_exception_raises_ProvisionException() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '__construct' => function() { throw new \lang\IllegalArgumentException('Test'); }
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test]
  public function field_with_inject_annotation_and_type() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject(type= "unittest.TestCase")] injected' => null,
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function field_with_inject_and_type_annotations() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject, @type("unittest.TestCase")] injected' => null,
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Unknown injection type/')]
  public function field_injecting_unbound() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject, @type("unittest.TestCase")] injected' => null,
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test]
  public function method_with_inject_annotation_and_type() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject(type= "unittest.TestCase")] inject' => function($param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function method_with_inject_annotation_and_restriction() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject] inject' => function(\unittest\TestCase $param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Unknown injection type/')]
  public function method_injecting_unbound() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject] inject' => function(\unittest\TestCase $param) { /* Empty */ }
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Test/')]
  public function method_throwing_an_exception_raises_ProvisionException() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject] inject' => function(\unittest\TestCase $param) { throw new \lang\IllegalArgumentException('Test'); }
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }
}