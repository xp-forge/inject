<?php namespace inject\unittest;

use inject\unittest\fixture\{Database, FileSystem, InMemory, Storage, Value};
use inject\{ConfiguredBindings, Injector};
use io\streams\MemoryInputStream;
use lang\ClassNotFoundException;
use unittest\{Assert, Expect, Test, Values};
use util\Properties;

class ConfiguredBindingsTest {

  #[Test]
  public function can_create_with_properties() {
    new ConfiguredBindings(new Properties('test.ini'));
  }

  #[Test]
  public function can_create_with_filename() {
    new ConfiguredBindings('test.ini');
  }

  /** @return util.Properties */
  private function loadProperties($input) {
    $p= new Properties(null);
    $p->load(new MemoryInputStream($input));
    return $p;
  }

  #[Test]
  public function bind_class() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem
    ')));
    Assert::equals(new FileSystem(), $inject->get(Storage::class));
  }

  #[Test]
  public function bind_instance() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem("/usr")
    ')));
    Assert::equals(new FileSystem('/usr'), $inject->get(Storage::class));
  }

  #[Test]
  public function bind_instance_without_constructor() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.InMemory()
    ')));
    Assert::equals(new InMemory(), $inject->get(Storage::class));
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function bind_class_to_nonexistant_impl() {
    new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=NonExistant
    ')));
  }

  #[Test, Values([['string[test]="Test"', 'string', 'Test'], ['int[test]=6100', 'int', 6100], ['double[test]=1.5', 'double', 1.5], ['bool[test]=true', 'bool', true], ['bool[test]=false', 'bool', false]])]
  public function bind_primitive($line, $type, $expected) {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties($line)));
    Assert::equals($expected, $inject->get($type, 'test'));
  }

  #[Test]
  public function bind_named_class() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage[files]=inject.unittest.fixture.FileSystem
    ')));
    Assert::equals(new FileSystem(), $inject->get(Storage::class, 'files'));
  }

  #[Test]
  public function bind_named_instance() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage[files]=inject.unittest.fixture.FileSystem("/usr")
    ')));
    Assert::equals(new FileSystem('/usr'), $inject->get(Storage::class, 'files'));
  }

  #[Test]
  public function bind_multiple() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage[user]=inject.unittest.fixture.FileSystem("~/.xp")
      inject.unittest.fixture.Storage[system]=inject.unittest.fixture.FileSystem("/etc/xp")
    ')));
    Assert::equals(
      [new FileSystem('~/.xp'), new FileSystem('/etc/xp')],
      [$inject->get(Storage::class, 'user'), $inject->get(Storage::class, 'system')]
    );
  }

  #[Test]
  public function two_arguments() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem("/path", true)
    ')));
    Assert::equals(new FileSystem('/path', true), $inject->get(Storage::class));
  }

  #[Test]
  public function array_argument() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.Database(["mysql://one", "mysql://two"])
    ')));
    Assert::equals(new Database(['mysql://one', 'mysql://two']), $inject->get(Storage::class));
  }

  #[Test]
  public function string_argument_containing_comma() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem("/path/with,commas/inside")
    ')));
    Assert::equals(new FileSystem('/path/with,commas/inside'), $inject->get(Storage::class));
  }

  #[Test]
  public function string_primitive_containing_comma() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      string[path]="/path/with,commas/inside"
    ')));
    Assert::equals('/path/with,commas/inside', $inject->get('string', 'path'));
  }

  #[Test, Values([['null', null], ['true', true], ['false', false], ['0', 0], ['-1', -1], ['1', 1], ['0.0', 0.0], ['-1.5', -1.5], ['1.5', 1.5], ['"test"', 'test'], ['"\"test\""', '"test"'], ["'test'", 'test'], ["'\'test\''", "'test'"]])]
  public function bind_instance_with($param, $expected) {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Value=inject.unittest.fixture.Value('.$param.')
    ')));
    Assert::equals(new Value($expected), $inject->get('inject.unittest.fixture.Value'));
  }

  #[Test, Values(['Storage=FileSystem', 'Storage=FileSystem()', 'Storage=inject.unittest.fixture.FileSystem', 'Storage=inject.unittest.fixture.FileSystem()', 'inject.unittest.fixture.Storage=FileSystem', 'inject.unittest.fixture.Storage=FileSystem()', 'inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem', 'inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()'])]
  public function namespace_import_via_use($line) {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      use[]=inject.unittest.fixture
      '.$line.'
    ')));
    Assert::equals(new FileSystem(), $inject->get(Storage::class));
  }

  #[Test]
  public function use_section() {
    $prop= $this->loadProperties('
      [one]
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()
    ');
    $inject= new Injector(new ConfiguredBindings($prop, 'one'));
    Assert::equals(new FileSystem(), $inject->get(Storage::class));
  }

  #[Test]
  public function use_different_section() {
    $prop= $this->loadProperties('
      [one]
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()
    ');
    $inject= new Injector(new ConfiguredBindings($prop, 'two'));
    Assert::null($inject->get(Storage::class));
  }

  #[Test]
  public function inheriting_binding_from_defaults() {
    $prop= $this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()

      [one]
   ');
    $inject= new Injector(new ConfiguredBindings($prop, 'one'));
    Assert::equals(new FileSystem(), $inject->get(Storage::class));
  }

  #[Test]
  public function overwriting_binding_from_defaults() {
    $prop= $this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()

      [one]
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem("/usr/local")
   ');
    $inject= new Injector(new ConfiguredBindings($prop, 'one'));
    Assert::equals(new FileSystem('/usr/local'), $inject->get(Storage::class));
  }
}