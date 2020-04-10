<?php namespace inject\unittest;

use inject\{Injector, InstanceBinding, Named};
use inject\unittest\fixture\Value;
use lang\Type;

class NamedTest extends \unittest\TestCase {

  #[@test]
  public function providing_named_values() {
    $inject= new Injector();
    $inject->bind(Value::class, newinstance(Named::class, [], [
      'provides' => function($name) { return true; },
      'binding'  => function($name) { return new InstanceBinding(new Value($name)); }
    ]));

    $this->assertEquals(new Value('default'), $inject->get(Value::class, 'default'));
  }

  #[@test]
  public function providing_without_name() {
    $inject= new Injector();
    $inject->bind(Value::class, newinstance(Named::class, [], [
      'provides' => function($name) { return true; },
      'binding'  => function($name) { return new InstanceBinding(new Value($name)); }
    ]));

    $this->assertEquals(new Value(null), $inject->get(Value::class));
  }

  #[@test]
  public function get_returns_null_if_provides_returns_false() {
    $inject= new Injector();
    $inject->bind(Value::class, newinstance(Named::class, [], [
      'provides' => function($name) { return false; },
      'binding'  => function($name) { throw new IllegalStateException('Should not be reached'); }
    ]));

    $this->assertNull($inject->get(Value::class, 'default'));
  }

  #[@test]
  public function using_a_provider() {
    $inject= new Injector();
    $inject->bind(Value::class, newinstance(Named::class, [], [
      'provides' => function($name) { return true; },
      'binding'  => function($name) { return new InstanceBinding(new Value($name)); }
    ]));

    $this->assertEquals(new Value('default'), $inject->get('inject.Provider<inject.unittest.fixture.Value>', 'default')->get());
  }
}