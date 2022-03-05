<?php namespace inject;

class Value implements Lookup {
  public static $ABSENT;
  private $value;

  static function __static() {
    self::$ABSENT= new self(null);
  }

  /** @param var $value */
  public function __construct($value) { $this->value= $value; }

  /**
   * Resolves this binding and returns the instance
   *
   * @param  inject.Injector $injector
   * @param  var
   */
  public function resolve($injector) {
    return $this->value;
  }
}