<?php namespace inject\unittest;

use inject\Injector;
use inject\InstanceProvider;
use inject\unittest\fixture\AbstractStorage;
use inject\unittest\fixture\FileSystem;
use inject\unittest\fixture\InMemory;
use inject\unittest\fixture\S3Bucket;
use inject\unittest\fixture\Storage;
use lang\ClassNotFoundException;
use lang\IllegalArgumentException;
use lang\XPClass;
use unittest\TestCase;
use unittest\actions\RuntimeVersion;
use util\Currency;

class InjectorTest extends TestCase {

  /** @return var[][] */
  protected function bindings() {
    $instance= new FileSystem();
    $name= Storage::class;
    return [
      [$name, XPClass::forName(FileSystem::class)],
      [$name, FileSystem::class],
      [$name, $instance],
      [XPClass::forName($name), XPClass::forName(FileSystem::class)],
      [XPClass::forName($name), FileSystem::class],
      [XPClass::forName($name), $instance]
    ];
  }

  /** @return var[][] */
  protected function storages() {
    return [
      [[XPClass::forName(FileSystem::class), XPClass::forName(InMemory::class)]],
      [[FileSystem::class, InMemory::class]],
      [[new FileSystem(), new InMemory()]]
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
    $this->assertEquals($inject, $inject->get(Injector::class));
  }

  #[@test, @values('bindings')]
  public function get_implementation_bound_to_interface($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl);
    $this->assertInstanceOf(FileSystem::class, $inject->get($type));
  }

  #[@test, @values('storages')]
  public function bind_array($impl) {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage[]', $impl);
    $this->assertEquals([new FileSystem(), new InMemory()], $inject->get('inject.unittest.fixture.Storage[]'));
  }

  #[@test]
  public function creates_implicit_binding_when_no_explicit_binding_exists_and_type_is_concrete() {
    $inject= new Injector();
    $impl= FileSystem::class;
    $this->assertInstanceOf($impl, $inject->get($impl));
  }

  #[@test]
  public function no_implicit_binding_for_interfaces() {
    $this->assertNull((new Injector())->get(Storage::class));
  }

  #[@test]
  public function no_implicit_binding_for_abstract_classes() {
    $this->assertNull((new Injector())->get(AbstractStorage::class));
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

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_bind_string_to_int() {
    $inject= new Injector();
    $inject->bind('string', 0x82523c0, 'API Key');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_bind_non_class_type_unnamed() {
    $inject= new Injector();
    $inject->bind('string', '82523c0');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_bind_non_concrete_implementation() {
    $inject= new Injector();
    $inject->bind(Storage::class, AbstractStorage::class);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_bind_uncompatible_instance() {
    $inject= new Injector();
    $inject->bind(Storage::class, $this);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_bind_uncompatible_class() {
    $inject= new Injector();
    $inject->bind(Storage::class, XPClass::forName(TestCase::class));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_bind_uncompatible_class_name() {
    $inject= new Injector();
    $inject->bind(Storage::class, TestCase::class);
  }

  #[@test, @expect(ClassNotFoundException::class)]
  public function cannot_bind_non_existant_class() {
    $inject= new Injector();
    $inject->bind(Storage::class, '@non.existant.class@');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_bind_array_type_to_non_array() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage[]', Storage::class);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_bind_non_array_type_to_array() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', [Storage::class]);
  }

  #[@test, @values('bindings')]
  public function get_named_implementation_bound_to_interface($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl, 'test');
    $this->assertInstanceOf(FileSystem::class, $inject->get($type, 'test'));
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
  public function get_given_a_typeunion_searches_all_types() {
    $fs= new FileSystem('/usr');
    $inject= new Injector();
    $inject->bind(FileSystem::class, $fs);
    $this->assertEquals($fs, $inject->get('string|inject.unittest.fixture.FileSystem'));
  }

  #[@test]
  public function get_given_a_typeunion_searches_all_named_types() {
    $path= '/usr';
    $inject= new Injector();
    $inject->bind('string', $path, 'path');
    $this->assertEquals($path, $inject->get('string|inject.unittest.fixture.FileSystem', 'path'));
  }

  #[@test]
  public function get_given_a_typeunion_searches_all_named_types_and_uses_first() {
    $path= '/usr';
    $inject= new Injector();
    $inject->bind('string', $path, 'path');
    $inject->bind(FileSystem::class, new FileSystem('/usr'));
    $this->assertEquals($path, $inject->get('string|inject.unittest.fixture.FileSystem', 'path'));
  }

  #[@test]
  public function primitive_array_type() {
    $path= ['/usr', '/usr/local'];
    $inject= new Injector();
    $inject->bind('string[]', $path, 'path');
    $this->assertEquals($path, $inject->get('string[]', 'path'));
  }

  #[@test, @action(new RuntimeVersion('>=7.0'))]
  public function typeunion_with_primitive_and_primitive_array_type() {
    $path= ['/usr', '/usr/local'];
    $inject= new Injector();
    $inject->bind('string[]', $path, 'path');
    $this->assertEquals($path, $inject->get('string|string[]', 'path'));
  }

  #[@test]
  public function uses_parameter_name() {
    $bucket= 's3+latest://id:secret@us-west-2';
    $inject= new Injector();
    $inject->bind('string', $bucket, 'bucket');
    $this->assertEquals(new S3Bucket($bucket), $inject->get(S3Bucket::class));
  }
}