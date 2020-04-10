<?php namespace inject\unittest;

use inject\{Bindings, Injector};
use inject\unittest\fixture\{FileSystem, Storage};
use unittest\TestCase;
use util\Currency;

class BindingsTest extends TestCase {
  protected $bindings;

  /**
   * Initializes bindings
   */
  public function setUp() {
    $this->bindings= newinstance(Bindings::class, [], [
      'configure' => function($inject) { $inject->bind(Storage::class, new FileSystem()); }
    ]);
  }

  #[@test]
  public function can_optionally_be_given_binding() {
    $inject= new Injector($this->bindings);
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
  }

  #[@test]
  public function can_optionally_be_given_bindings() {
    $inject= new Injector(
      $this->bindings,
      newinstance(Bindings::class, [], [
        'configure' => function($inject) { $inject->bind(Currency::class, Currency::$EUR, 'EUR'); }
      ])
    );
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
    $this->assertEquals(Currency::$EUR, $inject->get(Currency::class, 'EUR'));
  }

  #[@test]
  public function add_returns_injector() {
    $inject= new Injector();
    $this->assertEquals($inject, $inject->add($this->bindings));
  }

  #[@test]
  public function add() {
    $inject= new Injector();
    $inject->add($this->bindings);
    $this->assertInstanceOf(FileSystem::class, $inject->get(Storage::class));
  }
}