<?php namespace inject\unittest;

use inject\Injector;
use inject\InstanceProvider;
use lang\XPClass;
use unittest\TestCase;
use util\Currency;
use inject\unittest\fixture\FileSystem;

class InjectorTest extends TestCase {

  /** @return var[][] */
  protected function bindings() {
    $instance= new FileSystem();
    $name= 'inject.unittest.fixture.Storage';
    return [
      [$name, XPClass::forName('inject.unittest.fixture.FileSystem')],
      [$name, 'inject.unittest.fixture.FileSystem'],
      [$name, $instance],
      [XPClass::forName($name), XPClass::forName('inject.unittest.fixture.FileSystem')],
      [XPClass::forName($name), 'inject.unittest.fixture.FileSystem'],
      [XPClass::forName($name), $instance]
    ];
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
  public function binds_self_per_default() {
    $inject= new Injector();
    $this->assertEquals($inject, $inject->get('inject.Injector'));
  }

  #[@test, @values('bindings')]
  public function get_implementation_bound_to_interface($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl);
    $this->assertInstanceOf('inject.unittest.fixture.FileSystem', $inject->get($type));
  }

  #[@test]
  public function creates_implicit_binding_when_no_explicit_binding_exists_and_type_is_concrete() {
    $inject= new Injector();
    $impl= 'inject.unittest.fixture.FileSystem';
    $this->assertInstanceOf($impl, $inject->get($impl));
  }

  #[@test]
  public function no_implicit_binding_for_interfaces() {
    $this->assertNull((new Injector())->get('inject.unittest.fixture.Storage'));
  }

  #[@test]
  public function no_implicit_binding_for_abstract_classes() {
    $this->assertNull((new Injector())->get('inject.unittest.fixture.AbstractStorage'));
  }

  #[@test]
  public function bind_string_named() {
    $inject= new Injector();
    $inject->bind('string', '82523c0', 'API Key');
    $this->assertEquals('82523c0', $inject->get('string', 'API Key'));
  }

  #[@test]
  public function bind_int_named() {
    $inject= new Injector();
    $inject->bind('int', 4, 'Timeout');
    $this->assertEquals(4, $inject->get('int', 'Timeout'));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_bind_string_to_int() {
    $inject= new Injector();
    $inject->bind('string', 0x82523c0, 'API Key');
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_bind_non_class_type_unnamed() {
    $inject= new Injector();
    $inject->bind('string', '82523c0');
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_bind_non_concrete_implementation() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', 'inject.unittest.fixture.AbstractStorage');
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_bind_uncompatible_instance() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this);
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_bind_uncompatible_class() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', XPClass::forName('unittest.TestCase'));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_bind_uncompatible_class_name() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', 'unittest.TestCase');
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function cannot_bind_non_existant_class() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', '@non.existant.class@');
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
}