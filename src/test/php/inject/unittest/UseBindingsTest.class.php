<?php namespace inject\unittest;

use inject\Bindings;
use inject\Injector;
use inject\UseBindings;
use inject\unittest\fixture\FileSystem;
use inject\unittest\fixture\Storage;
use io\streams\MemoryInputStream;
use unittest\TestCase;
use util\Properties;

class UseBindingsTest extends TestCase {

  #[@test]
  public function using_creates_fluent_interface() {
    $this->assertInstanceOf(UseBindings::class, Bindings::using());
  }

  #[@test]
  public function typed() {
    $inject= new Injector(Bindings::using()->typed(Storage::class, FileSystem::class));
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
  }

  #[@test]
  public function typed_produces_different_instances() {
    $inject= new Injector(Bindings::using()->typed(Storage::class, FileSystem::class));
    $a= $inject->get(Storage::class);
    $b= $inject->get(Storage::class);
    $this->assertFalse($a === $b, 'same instance');
  }

  #[@test]
  public function singleton() {
    $inject= new Injector(Bindings::using()->singleton(Storage::class, FileSystem::class));
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
  }

  #[@test]
  public function singleton_produces_same_instance() {
    $inject= new Injector(Bindings::using()->singleton(Storage::class, FileSystem::class));
    $a= $inject->get(Storage::class);
    $b= $inject->get(Storage::class);
    $this->assertTrue($a === $b, 'same instance');
  }

  #[@test]
  public function named() {
    $cwd= new FileSystem('.');
    $inject= new Injector(Bindings::using()->named('cwd', $cwd));
    $this->assertEquals($cwd, $inject->get(FileSystem::class, 'cwd'));
  }

  #[@test]
  public function instance() {
    $cwd= new FileSystem('.');
    $inject= new Injector(Bindings::using()->instance($cwd));
    $this->assertEquals($cwd, $inject->get(FileSystem::class));
  }

  #[@test]
  public function properties() {
    $p= new Properties(null);
    $p->load(new MemoryInputStream('inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem'));
    $inject= new Injector(Bindings::using()->properties($p));
    $this->assertEquals(new FileSystem('/'), $inject->get(Storage::class));
  }
}