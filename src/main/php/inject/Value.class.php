<?php namespace inject;

class Value implements Lookup {
  public static $ABSENT;
  private $value;

  static function __static() {
    self::$ABSENT= new class() implements Lookup {
      public function get() { return null; }
      public function provided() { return null; }
    };
  }

  /** @param var $value */
  public function __construct($value) { $this->value= $value; }

  /** @return var */
  public function get() { return $this->value; }

  /** @return ?self */
  public function provided() { return $this; }
}