<?php namespace inject\unittest;

use inject\Bindings;
use inject\Injector;
use inject\unittest\fixture\FileSystem;
use util\Currency;
use unittest\TestCase;

class BindingsTest extends TestCase {

  #[@test]
  public function can_optionally_be_given_binding() {
    $inject= new Injector(newinstance('inject.Bindings', [], [
      'configure' => function($inject) { $inject->bind('inject.unittest.fixture.Storage', new FileSystem()); }
    ]));
    $this->assertInstanceOf('inject.unittest.fixture.FileSystem', $inject->get('inject.unittest.fixture.Storage'));
  }

  #[@test]
  public function can_optionally_be_given_bindings() {
    $inject= new Injector(
      newinstance('inject.Bindings', [], [
        'configure' => function($inject) { $inject->bind('inject.unittest.fixture.Storage', new FileSystem()); }
      ]),
      newinstance('inject.Bindings', [], [
        'configure' => function($inject) { $inject->bind('util.Currency', Currency::$EUR, 'EUR'); }
      ])
    );
    $this->assertInstanceOf('inject.unittest.fixture.FileSystem', $inject->get('inject.unittest.fixture.Storage'));
    $this->assertEquals(Currency::$EUR, $inject->get('util.Currency', 'EUR'));
  }
}