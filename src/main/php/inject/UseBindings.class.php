<?php namespace inject;

/**
 * Fluent interface
 *
 * @see   inject.Bindings::using()
 * @test  xp://inject.unittest.UseBindingsTest
 */
class UseBindings extends Bindings {
  private $bindings= [];

  /**
   * Binds a given type to a given implementing type, creating a new
   * instance every time `$inject->get()` is invoked.
   *
   * @param  string|lang.Type $type
   * @param  ?(string|lang.Type) $impl Defaults to type itself
   * @return self
   */
  public function typed($type, $impl= null) {
    $this->bindings[]= [$type, new ClassBinding($impl ?: $type)];
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
    $this->bindings[]= [$type, new SingletonBinding($impl ?: $type)];
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
    $this->bindings[]= [typeof($instance), new InstanceBinding($instance), $name];
    return $this;
  }

  /**
   * Binds a give instance to its type
   *
   * @param  var $instance
   * @return self
   */
  public function instance($instance) {
    $this->bindings[]= [typeof($instance), new InstanceBinding($instance)];
    return $this;
  }


  /**
   * Configures bindings on given injector
   *
   * @param  inject.Injector $injector
   */
  public function configure($injector) {
    foreach ($this->bindings as $b) {
      $injector->add(...$b);
    }
  }
}