<?php namespace inject\unittest;

use inject\unittest\fixture\{FileSystem, Storage};
use inject\{Bindings, Injector, UseBindings};
use io\streams\MemoryInputStream;
use test\{Assert, Test};
use util\Properties;

class UseBindingsTest {

  #[Test]
  public function using_creates_fluent_interface() {
    Assert::instance(UseBindings::class, Bindings::using());
  }

  #[Test]
  public function typed() {
    $inject= new Injector(Bindings::using()->typed(Storage::class, FileSystem::class));
    Assert::instance(FileSystem::class, $inject->get(Storage::class));
  }

  #[Test]
  public function typed_produces_different_instances() {
    $inject= new Injector(Bindings::using()->typed(Storage::class, FileSystem::class));
    $a= $inject->get(Storage::class);
    $b= $inject->get(Storage::class);
    Assert::false($a === $b, 'same instance');
  }

  #[Test]
  public function singleton() {
    $inject= new Injector(Bindings::using()->singleton(Storage::class, FileSystem::class));
    Assert::instance(FileSystem::class, $inject->get(Storage::class));
  }

  #[Test]
  public function singleton_uses_same_instance() {
    $fs= new FileSystem('/');
    $inject= new Injector(Bindings::using()->singleton(Storage::class, $fs));
    Assert::true($fs === $inject->get(Storage::class), 'same instance');
  }

  #[Test]
  public function singleton_produces_same_instance() {
    $inject= new Injector(Bindings::using()->singleton(Storage::class, FileSystem::class));
    $a= $inject->get(Storage::class);
    $b= $inject->get(Storage::class);
    Assert::true($a === $b, 'same instance');
  }

  #[Test]
  public function named() {
    $cwd= new FileSystem('.');
    $inject= new Injector(Bindings::using()->named('cwd', $cwd));
    Assert::equals($cwd, $inject->get(FileSystem::class, 'cwd'));
  }

  #[Test]
  public function instance() {
    $cwd= new FileSystem('.');
    $inject= new Injector(Bindings::using()->instance($cwd));
    Assert::equals($cwd, $inject->get(FileSystem::class));
  }

  #[Test]
  public function properties() {
    $p= new Properties(null);
    $p->load(new MemoryInputStream('inject.unittest.fixture.Storage=inject.unittest.fixture.FileSystem'));
    $inject= new Injector(Bindings::using()->properties($p));
    Assert::equals(new FileSystem('/'), $inject->get(Storage::class));
  }
}