<?php namespace inject\aop;

/**
 * Method matcher
 */
abstract class Methods extends \lang\Object {
  public static $ALL;

  static function __static() {
    self::$ALL= newinstance(__CLASS__, [], '{
      static function __static() { }
      public function match($routine) { return true; }
    }');
  }

  /**
   * Returns whether this matcher matches the given routine 
   *
   * @param  lang.reflect.Routine $routine
   * @return bool
   */
  abstract function match($routine);
}