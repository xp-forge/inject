<?php namespace inject;

/**
 * Base class for bindings. Extend from this class and overwrite its
 * `configure()` method.
 *
 * @test    xp://inject.unittest.BindingsTest
 */
abstract class Bindings {
  public static $ABSENT;

  static function __static() {
    self::$ABSENT ?? self::$ABSENT= new class() implements Binding {
      public function resolve($inject) { return null; }
      public function provider($inject) { return null; }
    };
  }

  /**
   * Creates a new fluent Bindings instance
   *
   * @return  self
   */
  public static function using() { return new UseBindings(); }

  /**
   * Configures bindings on given injector
   *
   * @param  inject.Injector $injector
   */
  public abstract function configure($injector);

}