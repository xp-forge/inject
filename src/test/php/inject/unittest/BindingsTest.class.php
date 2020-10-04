<?php namespace inject\unittest;

use inject\unittest\fixture\{FileSystem, Storage};
use inject\{Bindings, Injector};
use unittest\{Test, TestCase};
use util\Currency;

class BindingsTest extends TestCase {
  protected $bindings;

  /** @return void */
  public function setUp() {
    $this->bindings= new class() extends Bindings {
      public function configure($inject) {
        $inject->bind(Storage::class, new FileSystem());
      }
    };
  }

  #[Test]
  public function can_optionally_be_given_binding() {
    $inject= new Injector($this->bindings);
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
  }

  #[Test]
  public function can_optionally_be_given_bindings() {
    $inject= new Injector($this->bindings, new class() extends Bindings {
      public function configure($inject) {
        $inject->bind(Currency::class, Currency::$EUR, 'EUR');
      }
    });
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
    $this->assertEquals(Currency::$EUR, $inject->get(Currency::class, 'EUR'));
  }

  #[Test]
  public function add_returns_injector() {
    $inject= new Injector();
    $this->assertEquals($inject, $inject->add($this->bindings));
  }

  #[Test]
  public function add() {
    $inject= new Injector();
    $inject->add($this->bindings);
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
  }
}