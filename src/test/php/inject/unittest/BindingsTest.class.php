<?php namespace inject\unittest;

use inject\Bindings;
use inject\Injector;
use inject\unittest\fixture\FileSystem;
use util\Currency;
use unittest\TestCase;

class BindingsTest extends TestCase {
  protected $bindings;

  /**
   * Initializes bindings
   */
  public function setUp() {
    $this->bindings= newinstance('inject.Bindings', [], [
      'configure' => function($inject) { $inject->bind('inject.unittest.fixture.Storage', new FileSystem()); }
    ]);
  }

  #[@test]
  public function can_optionally_be_given_binding() {
    $inject= new Injector($this->bindings);
    $this->assertInstanceOf('inject.unittest.fixture.FileSystem', $inject->get('inject.unittest.fixture.Storage'));
  }

  #[@test]
  public function can_optionally_be_given_bindings() {
    $inject= new Injector(
      $this->bindings,
      newinstance('inject.Bindings', [], [
        'configure' => function($inject) { $inject->bind('util.Currency', Currency::$EUR, 'EUR'); }
      ])
    );
    $this->assertInstanceOf('inject.unittest.fixture.FileSystem', $inject->get('inject.unittest.fixture.Storage'));
    $this->assertEquals(Currency::$EUR, $inject->get('util.Currency', 'EUR'));
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
    $this->assertInstanceOf('inject.unittest.fixture.FileSystem', $inject->get('inject.unittest.fixture.Storage'));
  }
}