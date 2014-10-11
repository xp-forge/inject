<?php namespace inject;

/**
 * Base class for bindings. Extend from this class and overwrite its
 * `configure()` method.
 *
 * @test    xp://inject.unittest.BindingsTest
 */
abstract class Bindings extends \lang\Object {

  /**
   * Configures bindings on given injector
   *
   * @param  inject.Injector $injector
   */
  public abstract function configure($injector);

}