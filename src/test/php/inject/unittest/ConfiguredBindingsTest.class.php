<?php namespace inject\unittest;

use inject\Injector;
use inject\ConfiguredBindings;
use util\Properties;
use inject\unittest\fixture\Value;
use inject\unittest\fixture\FileSystem;

class ConfiguredBindingsTest extends \unittest\TestCase {

  #[@test]
  public function can_create_with_properties() {
    new ConfiguredBindings(new Properties('test.ini'));
  }

  #[@test]
  public function can_create_with_filename() {
    new ConfiguredBindings('test.ini');
  }

  #[@test]
  public function bind_class() {
    $inject= new Injector(new ConfiguredBindings(Properties::fromString('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem
    ')));
    $this->assertEquals(new FileSystem(), $inject->get('inject.unittest.fixture.Storage'));
  }

  #[@test]
  public function bind_instance() {
    $inject= new Injector(new ConfiguredBindings(Properties::fromString('
      inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem("/usr")
    ')));
    $this->assertEquals(new FileSystem('/usr'), $inject->get('inject.unittest.fixture.Storage'));
  }

  #[@test, @values([
  #  ['string[test]="Test"', 'string', 'Test'],
  #  ['int[test]=6100', 'int', 6100],
  #  ['double[test]=1.5', 'double', 1.5],
  #  ['bool[test]=true', 'bool', true],
  #  ['bool[test]=false', 'bool', false]
  #])]
  public function bind_primitive($line, $type, $expected) {
    $inject= new Injector(new ConfiguredBindings(Properties::fromString($line)));
    $this->assertEquals($expected, $inject->get($type, 'test'));
  }

  #[@test]
  public function bind_named_class() {
    $inject= new Injector(new ConfiguredBindings(Properties::fromString('
      inject.unittest.fixture.Storage[files]=inject.unittest.fixture.FileSystem
    ')));
    $this->assertEquals(new FileSystem(), $inject->get('inject.unittest.fixture.Storage', 'files'));
  }

  #[@test]
  public function bind_named_instance() {
    $inject= new Injector(new ConfiguredBindings(Properties::fromString('
      inject.unittest.fixture.Storage[files]=inject.unittest.fixture.FileSystem("/usr")
    ')));
    $this->assertEquals(new FileSystem('/usr'), $inject->get('inject.unittest.fixture.Storage', 'files'));
  }

  #[@test]
  public function bind_multiple() {
    $inject= new Injector(new ConfiguredBindings(Properties::fromString('
      inject.unittest.fixture.Storage[user]=inject.unittest.fixture.FileSystem("~/.xp")
      inject.unittest.fixture.Storage[system]=inject.unittest.fixture.FileSystem("/etc/xp")
    ')));
    $this->assertEquals(
      [new FileSystem('~/.xp'), new FileSystem('/etc/xp')],
      [$inject->get('inject.unittest.fixture.Storage', 'user'), $inject->get('inject.unittest.fixture.Storage', 'system')]
    );
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
    $inject= new Injector(new ConfiguredBindings(Properties::fromString('
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
  public function uses_propertyfile_sections_as_namespace($line) {
    $inject= new Injector(new ConfiguredBindings(Properties::fromString('
      [inject.unittest.fixture]
      '.$line.'
    ')));
    $this->assertEquals(new FileSystem(), $inject->get('inject.unittest.fixture.Storage'));
  }
}