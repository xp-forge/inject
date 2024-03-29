<?php namespace inject;

use lang\Type;
use util\PropertyAccess;

/**
 * Fluent interface
 *
 * @see   inject.Bindings::using()
 * @test  xp://inject.unittest.UseBindingsTest
 */
class UseBindings extends Bindings {
  private $configure= [];

  /**
   * Binds a given type to a given implementing type, creating a new
   * instance every time `$inject->get()` is invoked.
   *
   * @param  string|lang.Type $type
   * @param  ?(string|lang.Type) $impl Defaults to type itself
   * @return self
   */
  public function typed($type, $impl= null) {
    $this->configure[]= function($injector) use($type, $impl) {
      $injector->bind($type, new ClassBinding($impl ?: $type));
    };
    return $this;
  }

  /**
   * Binds a given type to a given implementing type, ensuring only a
   * single instance of it will exist.
   *
   * @param  string|lang.Type $type
   * @param  ?(string|lang.Type) $impl Defaults to type itself
   * @return self
   */
  public function singleton($type, $impl= null) {
    $this->configure[]= function($injector) use($type, $impl) {
      if (null === $impl) {
        $injector->bind($type, new SingletonBinding($type));
      } else if (is_string($impl) || $impl instanceof Type) {
        $injector->bind($type, new SingletonBinding($impl));
      } else {
        $injector->bind($type, new InstanceBinding($impl));
      }
    };
    return $this;
  }

  /**
   * Binds a give instance to its type and a given name
   *
   * @param  string $name
   * @param  var $instance
   * @return self
   */
  public function named($name, $instance) {
    $this->configure[]= function($injector) use($instance, $name) {
      $injector->bind(typeof($instance), new InstanceBinding($instance), $name);
    };
    return $this;
  }

  /**
   * Binds a given instance to its type.
   *
   * @param  var $instance
   * @return self
   */
  public function instance($instance) {
    $this->configure[]= function($injector) use($instance) {
      $injector->bind(typeof($instance), new InstanceBinding($instance));
    };
    return $this;
  }

  /**
   * Add configured bindings
   *
   * @param  util.PropertyAccess $properties
   * @return self
   */
  public function properties(PropertyAccess $properties) {
    $this->configure[]= function($injector) use($properties) {
      $injector->add(new ConfiguredBindings($properties));
    };
    return $this;
  }

  /**
   * Configures bindings on given injector
   *
   * @param  inject.Injector $injector
   */
  public function configure($injector) {
    foreach ($this->configure as $configure) {
      $configure($injector);
    }
  }
}