<?php namespace inject\unittest;

use inject\Injector;
use inject\InstanceBinding;
use inject\unittest\fixture\Value;
use lang\Type;

class NamedTest extends \unittest\TestCase {

  #[@test]
  public function providing_named_values() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Value', newinstance('inject.Named', [], [
      'provides' => function($name) { return true; },
      'binding'  => function($name) { return new InstanceBinding(new Value($name)); }
    ]));

    $this->assertEquals(new Value('default'), $inject->get('inject.unittest.fixture.Value', 'default'));
  }

  #[@test]
  public function get_returns_null_if_provides_returns_false() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Value', newinstance('inject.Named', [], [
      'provides' => function($name) { return false; },
      'binding'  => function($name) { throw new IllegalStateException('Should not be reached'); }
    ]));

    $this->assertNull($inject->get('inject.unittest.fixture.Value', 'default'));
  }
}