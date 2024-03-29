<?php namespace inject\unittest;

use inject\unittest\fixture\{FileSystem, Storage, UseProvider};
use inject\{Injector, InstanceProvider, ResolvingProvider, TypeProvider};
use lang\{Type, XPClass};
use test\{Assert, Test};

class ProvidersTest {

  #[Test]
  public function type_provider() {
    $inject= new Injector();
    $inject->bind(Storage::class, XPClass::forName(FileSystem::class));
    Assert::instance(
      'inject.TypeProvider',
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')
    );
  }

  #[Test]
  public function type_provider_get() {
    $inject= new Injector();
    $inject->bind(Storage::class, XPClass::forName(FileSystem::class));
    Assert::instance(
      FileSystem::class,
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')->get()
    );
  }

  #[Test]
  public function type_provider_bound_to_type_provider() {
    $inject= new Injector();
    $provider= new TypeProvider(XPClass::forName(FileSystem::class), $inject);
    $inject->bind(Storage::class, $provider);
    Assert::equals($provider, $inject->get('inject.Provider<inject.unittest.fixture.Storage>'));
  }

  #[Test]
  public function type_bound_to_type_provider() {
    $inject= new Injector();
    $provider= new TypeProvider(XPClass::forName(FileSystem::class), $inject);
    $inject->bind(Storage::class, $provider);
    Assert::instance(
      FileSystem::class,
      $inject->get(Storage::class)
    );
  }

  #[Test]
  public function instance_provider() {
    $inject= new Injector();
    $inject->bind(Storage::class, new FileSystem());
    Assert::instance(
      InstanceProvider::class,
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')
    );
  }

  #[Test]
  public function instance_provider_get() {
    $inject= new Injector();
    $inject->bind(Storage::class, new FileSystem());
    Assert::instance(
      FileSystem::class,
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')->get()
    );
  }

  #[Test]
  public function resolving_provider_used_for_arrays() {
    $storages= [new FileSystem()];

    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage[]', $storages);
    Assert::equals($storages, $inject->get('inject.Provider<inject.unittest.fixture.Storage[]>')->get());
  }

  #[Test]
  public function parameter_with_provider() {
    $inject= new Injector();
    $inject->bind(Storage::class, new FileSystem());

    Assert::instance(InstanceProvider::class, $inject->get(UseProvider::class)->provider);
  }
}