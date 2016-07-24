<?php namespace inject\unittest;

use inject\Injector;
use inject\InstanceProvider;
use inject\TypeProvider;
use unittest\TestCase;
use lang\XPClass;
use inject\unittest\fixture\FileSystem;
use inject\unittest\fixture\Storage;

class ProvidersTest extends TestCase {

  #[@test]
  public function type_provider() {
    $inject= new Injector();
    $inject->bind(Storage::class, XPClass::forName(FileSystem::class));
    $this->assertInstanceOf(
      'inject.TypeProvider',
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')
    );
  }

  #[@test]
  public function type_provider_get() {
    $inject= new Injector();
    $inject->bind(Storage::class, XPClass::forName(FileSystem::class));
    $this->assertInstanceOf(
      FileSystem::class,
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')->get()
    );
  }

  #[@test]
  public function type_provider_bound_to_type_provider() {
    $inject= new Injector();
    $provider= new TypeProvider(XPClass::forName(FileSystem::class), $inject);
    $inject->bind(Storage::class, $provider);
    $this->assertEquals($provider, $inject->get('inject.Provider<inject.unittest.fixture.Storage>'));
  }

  #[@test]
  public function type_bound_to_type_provider() {
    $inject= new Injector();
    $provider= new TypeProvider(XPClass::forName(FileSystem::class), $inject);
    $inject->bind(Storage::class, $provider);
    $this->assertInstanceOf(
      FileSystem::class,
      $inject->get(Storage::class)
    );
  }

  #[@test]
  public function instance_provider() {
    $inject= new Injector();
    $inject->bind(Storage::class, new FileSystem());
    $this->assertInstanceOf(
      InstanceProvider::class,
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')
    );
  }

  #[@test]
  public function instance_provider_get() {
    $inject= new Injector();
    $inject->bind(Storage::class, new FileSystem());
    $this->assertInstanceOf(
      FileSystem::class,
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')->get()
    );
  }
}