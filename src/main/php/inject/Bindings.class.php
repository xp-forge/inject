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

  /**
   * Executes bindings and returns injector
   *
   * @return inject.Injector
   */
  public function injector() {
    $injector= new Injector();
    $this->bind($injector);
    return $injector;
  }
}