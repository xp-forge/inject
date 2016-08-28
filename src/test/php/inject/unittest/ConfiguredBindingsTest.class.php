<?php namespace inject\unittest;

use inject\Injector;
use inject\ConfiguredBindings;
use util\Properties;
use inject\unittest\fixture\Value;
use inject\unittest\fixture\FileSystem;
use inject\unittest\fixture\Storage;
use io\streams\MemoryInputStream;

class ConfiguredBindingsTest extends \unittest\TestCase {

  #[@test]
  public function can_create_with_properties() {
    new ConfiguredBindings(new Properties('test.ini'));
  }

  #[@test]
  public function can_create_with_filename() {
    new ConfiguredBindings('test.ini');
  }

  /** @return util.Properties */
  private function loadProperties($input) {
    $p= new Properties(null);
    $p->load(new MemoryInputStream($input));
    return $p;
  }

  #[@test]
  public function bind_class() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem
    ')));
    $this->assertEquals(new FileSystem(), $inject->get(Storage::class));
  }

  #[@test]
  public function bind_instance() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem("/usr")
    ')));
    $this->assertEquals(new FileSystem('/usr'), $inject->get(Storage::class));
  }

  #[@test, @values([
  #  ['string[test]="Test"', 'string', 'Test'],
  #  ['int[test]=6100', 'int', 6100],
  #  ['double[test]=1.5', 'double', 1.5],
  #  ['bool[test]=true', 'bool', true],
  #  ['bool[test]=false', 'bool', false]
  #])]
  public function bind_primitive($line, $type, $expected) {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties($line)));
    $this->assertEquals($expected, $inject->get($type, 'test'));
  }

  #[@test]
  public function bind_named_class() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage[files]=inject.unittest.fixture.FileSystem
    ')));
    $this->assertEquals(new FileSystem(), $inject->get(Storage::class, 'files'));
  }

  #[@test]
  public function bind_named_instance() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage[files]=inject.unittest.fixture.FileSystem("/usr")
    ')));
    $this->assertEquals(new FileSystem('/usr'), $inject->get(Storage::class, 'files'));
  }

  #[@test]
  public function bind_multiple() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage[user]=inject.unittest.fixture.FileSystem("~/.xp")
      inject.unittest.fixture.Storage[system]=inject.unittest.fixture.FileSystem("/etc/xp")
    ')));
    $this->assertEquals(
      [new FileSystem('~/.xp'), new FileSystem('/etc/xp')],
      [$inject->get(Storage::class, 'user'), $inject->get(Storage::class, 'system')]
    );
  }

  #[@test]
  public function string_argument_containing_comma() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem("/path/with,commas/inside")
    ')));
    $this->assertEquals(new FileSystem('/path/with,commas/inside'), $inject->get(Storage::class));
  }

  #[@test]
  public function string_primitive_containing_comma() {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      string[path]="/path/with,commas/inside"
    ')));
    $this->assertEquals('/path/with,commas/inside', $inject->get('string', 'path'));
  }

  #[@test, @values([
  #  ['null', null],
  #  ['true', true],
  #  ['false', false],
  #  ['0', 0], ['-1', -1], ['1', 1],
  #  ['0.0', 0.0], ['-1.5', -1.5], ['1.5', 1.5],
  #  ['"test"', 'test'], ['"\"test\""', '"test"'],
  #  ["'test'", 'test'], ["'\'test\''", "'test'"]
  #])]
  public function bind_instance_with($param, $expected) {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      inject.unittest.fixture.Value=inject.unittest.fixture.Value('.$param.')
    ')));
    $this->assertEquals(new Value($expected), $inject->get('inject.unittest.fixture.Value'));
  }

  #[@test, @values([
  #  'Storage=FileSystem',
  #  'Storage=FileSystem()',
  #  'Storage=inject.unittest.fixture.FileSystem',
  #  'Storage=inject.unittest.fixture.FileSystem()',
  #  'inject.unittest.fixture.Storage=FileSystem',
  #  'inject.unittest.fixture.Storage=FileSystem()',
  #  'inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem',
  #  'inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()'
  #])]
  public function namespace_import_via_use($line) {
    $inject= new Injector(new ConfiguredBindings($this->loadProperties('
      use[]=inject.unittest.fixture
      '.$line.'
    ')));
    $this->assertEquals(new FileSystem(), $inject->get(Storage::class));
  }

  #[@test]
  public function use_section() {
    $prop= $this->loadProperties('
      [one]
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()
    ');
    $inject= new Injector(new ConfiguredBindings($prop, 'one'));
    $this->assertEquals(new FileSystem(), $inject->get(Storage::class));
  }

  #[@test]
  public function use_different_section() {
    $prop= $this->loadProperties('
      [one]
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()
    ');
    $inject= new Injector(new ConfiguredBindings($prop, 'two'));
    $this->assertNull($inject->get(Storage::class));
  }

  #[@test]
  public function inheriting_binding_from_defaults() {
    $prop= $this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()

      [one]
   ');
    $inject= new Injector(new ConfiguredBindings($prop, 'one'));
    $this->assertEquals(new FileSystem(), $inject->get(Storage::class));
  }

  #[@test]
  public function overwriting_binding_from_defaults() {
    $prop= $this->loadProperties('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem()

      [one]
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem("/usr/local")
   ');
    $inject= new Injector(new ConfiguredBindings($prop, 'one'));
    $this->assertEquals(new FileSystem('/usr/local'), $inject->get(Storage::class));
  }
}