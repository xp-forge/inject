<?php namespace inject\unittest;

use inject\unittest\fixture\{AbstractStorage, FileSystem, InMemory, S3Bucket, Storage, URI};
use inject\{Injector, InstanceProvider, ProvisionException};
use lang\{ClassNotFoundException, IllegalArgumentException, XPClass};
use unittest\{Expect, Test, TestCase, Values};
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

  #[Test]
  public function can_create() {
    new Injector();
  }

  #[Test, Values('bindings')]
  public function bind_interface_to_implementation($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl);
  }

  #[Test, Values('bindings')]
  public function bind_returns_injector_instance($type, $impl) {
    $inject= new Injector();
    $this->assertEquals($inject, $inject->bind($type, $impl));
  }

  #[Test]
  public function binds_self_per_default() {
    $inject= new Injector();
    $this->assertEquals($inject, $inject->get(Injector::class));
  }

  #[Test, Values('bindings')]
  public function get_implementation_bound_to_interface($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl);
    $this->assertInstanceOf(FileSystem::class, $inject->get($type));
  }

  #[Test, Values('storages')]
  public function bind_array($impl) {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage[]', $impl);
    $this->assertEquals([new FileSystem(), new InMemory()], $inject->get('inject.unittest.fixture.Storage[]'));
  }

  #[Test]
  public function creates_implicit_binding_when_no_explicit_binding_exists_and_type_is_concrete() {
    $inject= new Injector();
    $impl= FileSystem::class;
    $this->assertInstanceOf($impl, $inject->get($impl));
  }

  #[Test]
  public function no_implicit_binding_for_interfaces() {
    $this->assertNull((new Injector())->get(Storage::class));
  }

  #[Test]
  public function no_implicit_binding_for_abstract_classes() {
    $this->assertNull((new Injector())->get(AbstractStorage::class));
  }

  #[Test]
  public function bind_string_named() {
    $inject= new Injector();
    $inject->bind('string', '82523c0', 'API Key');
    $this->assertEquals('82523c0', $inject->get('string', 'API Key'));
  }

  #[Test]
  public function bind_int_named() {
    $inject= new Injector();
    $inject->bind('int', 4, 'Timeout');
    $this->assertEquals(4, $inject->get('int', 'Timeout'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_bind_string_to_int() {
    $inject= new Injector();
    $inject->bind('string', 0x82523c0, 'API Key');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_bind_non_class_type_unnamed() {
    $inject= new Injector();
    $inject->bind('string', '82523c0');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_bind_non_concrete_implementation() {
    $inject= new Injector();
    $inject->bind(Storage::class, AbstractStorage::class);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_bind_uncompatible_instance() {
    $inject= new Injector();
    $inject->bind(Storage::class, $this);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_bind_uncompatible_class() {
    $inject= new Injector();
    $inject->bind(Storage::class, XPClass::forName(TestCase::class));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_bind_uncompatible_class_name() {
    $inject= new Injector();
    $inject->bind(Storage::class, TestCase::class);
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function cannot_bind_non_existant_class() {
    $inject= new Injector();
    $inject->bind(Storage::class, '@non.existant.class@');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_bind_array_type_to_non_array() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage[]', Storage::class);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_bind_non_array_type_to_array() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', [Storage::class]);
  }

  #[Test, Values('bindings')]
  public function get_named_implementation_bound_to_interface($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl, 'test');
    $this->assertInstanceOf(FileSystem::class, $inject->get($type, 'test'));
  }

  #[Test, Values('bindings')]
  public function get_unbound_named_type_returns_null($type, $impl) {
    $inject= new Injector();
    $this->assertNull($inject->get($type, 'any-name-really'));
  }

  #[Test, Values('bindings')]
  public function get_type_bound_by_different_name_returns_null($type, $impl) {
    $inject= new Injector();
    $inject->bind($type, $impl, 'test');
    $this->assertNull($inject->get($type, 'another-name-than-the-one-bound'));
  }

  #[Test]
  public function get_given_a_typeunion_searches_all_types() {
    $fs= new FileSystem('/usr');
    $inject= new Injector();
    $inject->bind(FileSystem::class, $fs);
    $this->assertEquals($fs, $inject->get('string|inject.unittest.fixture.FileSystem'));
  }

  #[Test]
  public function get_given_a_typeunion_searches_all_named_types() {
    $path= '/usr';
    $inject= new Injector();
    $inject->bind('string', $path, 'path');
    $this->assertEquals($path, $inject->get('string|inject.unittest.fixture.FileSystem', 'path'));
  }

  #[Test]
  public function get_given_a_typeunion_searches_all_named_types_and_uses_first() {
    $path= '/usr';
    $inject= new Injector();
    $inject->bind('string', $path, 'path');
    $inject->bind(FileSystem::class, new FileSystem('/usr'));
    $this->assertEquals($path, $inject->get('string|inject.unittest.fixture.FileSystem', 'path'));
  }

  #[Test]
  public function primitive_array_type() {
    $path= ['/usr', '/usr/local'];
    $inject= new Injector();
    $inject->bind('string[]', $path, 'path');
    $this->assertEquals($path, $inject->get('string[]', 'path'));
  }

  #[Test]
  public function typeunion_with_primitive_and_primitive_array_type() {
    $path= ['/usr', '/usr/local'];
    $inject= new Injector();
    $inject->bind('string[]', $path, 'path');
    $this->assertEquals($path, $inject->get('string|string[]', 'path'));
  }

  #[Test]
  public function uses_parameter_name() {
    $bucket= 's3+latest://id:secret@us-west-2';
    $inject= new Injector();
    $inject->bind('string', $bucket, 'bucket');
    $this->assertEquals(new S3Bucket($bucket), $inject->get(S3Bucket::class));
  }

  #[Test]
  public function detects_lookup_loops() {
    $inject= new Injector();
    try {
      $inject->get(URI::class);
      $this->fail('No exception raised', null, IllegalArgumentException::class);
    } catch (ProvisionException $expected) {

      // Determine source cause
      do {
        $source= $expected;
      } while ($expected= $expected->getCause());

      $this->assertEquals(
        'Exception lang.IllegalArgumentException (Lookup loop created by inject.unittest.fixture.URI@)',
        $source->compoundMessage()
      );
    }
  }
}