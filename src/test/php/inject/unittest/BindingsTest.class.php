<?php namespace inject\unittest;

use inject\Bindings;
use inject\unittest\fixture\FileSystem;
use unittest\TestCase;

class BindingsTest extends TestCase {

  #[@test]
  public function injector_instance() {
    $bindings= newinstance('inject.Bindings', [], [
      'bind' => function($injector) { }
    ]);
    $this->assertInstanceOf('inject.Injector', $bindings->injector());
  }

  #[@test]
  public function bind_one() {
    $bindings= newinstance('inject.Bindings', [], [
      'bind' => function($injector) {
        $injector->bind('inject.unittest.fixture.Storage', new FileSystem());
      }
    ]);
    $this->assertInstanceOf(
      'inject.unittest.fixture.FileSystem',
      $bindings->injector()->get('inject.unittest.fixture.Storage')
    );
  }
}