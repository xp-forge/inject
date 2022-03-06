<?php namespace inject\unittest;

use inject\unittest\fixture\{Storage, FileSystem};
use inject\{Injector, Bindings, ArrayBinding, ClassBinding, InstanceBinding, ProviderBinding, Provider};
use unittest\{Assert, Test};

/** Tests Injector::binding() */
class BindingTest {

  #[Test]
  public function primitive() {
    $inject= new Injector();
    $inject->bind('string', 'Test', 'test');

    Assert::instance(InstanceBinding::class, $inject->binding('string', 'test'));
  }

  #[Test]
  public function instance() {
    $inject= new Injector();
    $inject->bind(Storage::class, new FileSystem('/usr'));

    Assert::instance(InstanceBinding::class, $inject->binding(Storage::class));
  }

  #[Test]
  public function implicit() {
    $inject= new Injector();
    $inject->bind('string', '/usr', 'path');

    Assert::instance(InstanceBinding::class, $inject->binding(FileSystem::class));
  }

  #[Test]
  public function type() {
    $inject= new Injector();
    $inject->bind(Storage::class, FileSystem::class);

    Assert::instance(ClassBinding::class, $inject->binding(Storage::class));
  }

  #[Test]
  public function array() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage[]', [FileSystem::class]);

    Assert::instance(ArrayBinding::class, $inject->binding('inject.unittest.fixture.Storage[]'));
  }

  #[Test]
  public function provider() {
    $inject= new Injector();
    $inject->bind(Storage::class, new class() implements Provider {
      public function get() { return new FileSystem('/usr'); }
    });

    Assert::instance(ProviderBinding::class, $inject->binding(Storage::class));
  }

  #[Test]
  public function not_found() {
    $inject= new Injector();

    Assert::equals(Bindings::$ABSENT, $inject->binding(Storage::class));
  }
}