<?php namespace inject\unittest;

use inject\unittest\fixture\{FileSystem, Storage};
use inject\{Bindings, Injector};
use test\{Assert, Before, Test};
use util\Currency;

class BindingsTest {
  protected $bindings;

  #[Before]
  public function bindings() {
    $this->bindings= new class() extends Bindings {
      public function configure($inject) {
        $inject->bind(Storage::class, new FileSystem());
      }
    };
  }

  #[Test]
  public function can_optionally_be_given_binding() {
    $inject= new Injector($this->bindings);
    Assert::instance(FileSystem::class, $inject->get(Storage::class));
  }

  #[Test]
  public function can_optionally_be_given_bindings() {
    $inject= new Injector($this->bindings, new class() extends Bindings {
      public function configure($inject) {
        $inject->bind(Currency::class, Currency::$EUR, 'EUR');
      }
    });
    Assert::instance(FileSystem::class, $inject->get(Storage::class));
    Assert::equals(Currency::$EUR, $inject->get(Currency::class, 'EUR'));
  }

  #[Test]
  public function add_returns_injector() {
    $inject= new Injector();
    Assert::equals($inject, $inject->add($this->bindings));
  }

  #[Test]
  public function add() {
    $inject= new Injector();
    $inject->add($this->bindings);
    Assert::instance(FileSystem::class, $inject->get(Storage::class));
  }
}