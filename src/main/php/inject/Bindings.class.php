<?php namespace inject;

/**
 * Base class for bindings
 *
 * @test    xp://inject.unittest.BindingsTest
 */
abstract class Bindings extends \lang\Object {

  /**
   * Executes bindings on given injector
   *
   * @param  inject.Injector $injector
   */
  public abstract function bind($injector);

}