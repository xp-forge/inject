<?php namespace inject\unittest;

use inject\unittest\fixture\{FileSystem, Storage};
use inject\{Bindings, Injector, UseBindings};
use io\streams\MemoryInputStream;
use unittest\{Test, TestCase};
use util\Properties;

class UseBindingsTest extends TestCase {

  #[Test]
  public function using_creates_fluent_interface() {
    $this->assertInstanceOf(UseBindings::class, Bindings::using());
  }

  #[Test]
  public function typed() {
    $inject= new Injector(Bindings::using()->typed(Storage::class, FileSystem::class));
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
  }

  #[Test]
  public function typed_produces_different_instances() {
    $inject= new Injector(Bindings::using()->typed(Storage::class, FileSystem::class));
    $a= $inject->get(Storage::class);
    $b= $inject->get(Storage::class);
    $this->assertFalse($a === $b, 'same instance');
  }

  #[Test]
  public function singleton() {
    $inject= new Injector(Bindings::using()->singleton(Storage::class, FileSystem::class));
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
  }

  #[Test]
  public function singleton_produces_same_instance() {
    $inject= new Injector(Bindings::using()->singleton(Storage::class, FileSystem::class));
    $a= $inject->get(Storage::class);
    $b= $inject->get(Storage::class);
    $this->assertTrue($a === $b, 'same instance');
  }

  #[Test]
  public function named() {
    $cwd= new FileSystem('.');
    $inject= new Injector(Bindings::using()->named('cwd', $cwd));
    $this->assertEquals($cwd, $inject->get(FileSystem::class, 'cwd'));
  }

  #[Test]
  public function instance() {
    $cwd= new FileSystem('.');
    $inject= new Injector(Bindings::using()->instance($cwd));
    $this->assertEquals($cwd, $inject->get(FileSystem::class));
  }

  #[Test]
  public function properties() {
    $p= new Properties(null);
    $p->load(new MemoryInputStream('inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem'));
    $inject= new Injector(Bindings::using()->properties($p));
    $this->assertEquals(new FileSystem('/'), $inject->get(Storage::class));
  }
}