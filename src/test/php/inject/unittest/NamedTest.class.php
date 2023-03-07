<?php namespace inject\unittest;

use inject\unittest\fixture\Value;
use inject\{Injector, InstanceBinding, Named};
use lang\Type;
use test\{Assert, Test};

class NamedTest {

  #[Test]
  public function providing_named_values() {
    $inject= new Injector();
    $inject->bind(Value::class, new class() extends Named {
      public function provides($name) { return true; }
      public function binding($name) { return new InstanceBinding(new Value($name)); }
    });

    Assert::equals(new Value('default'), $inject->get(Value::class, 'default'));
  }

  #[Test]
  public function providing_without_name() {
    $inject= new Injector();
    $inject->bind(Value::class, new class() extends Named {
      public function provides($name) { return true; }
      public function binding($name) { return new InstanceBinding(new Value($name)); }
    });

    Assert::equals(new Value(null), $inject->get(Value::class));
  }

  #[Test]
  public function get_returns_null_if_provides_returns_false() {
    $inject= new Injector();
    $inject->bind(Value::class, new class() extends Named {
      public function provides($name) { return false; }
      public function binding($name) { throw new IllegalStateException('Should not be reached'); }
    });

    Assert::null($inject->get(Value::class, 'default'));
  }

  #[Test]
  public function using_a_provider() {
    $inject= new Injector();
    $inject->bind(Value::class, new class() extends Named {
      public function provides($name) { return true; }
      public function binding($name) { return new InstanceBinding(new Value($name)); }
    });

    Assert::equals(new Value('default'), $inject->get('inject.Provider<inject.unittest.fixture.Value>', 'default')->get());
  }
}