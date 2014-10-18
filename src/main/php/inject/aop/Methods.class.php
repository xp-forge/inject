<?php namespace inject\aop;

use lang\Type;

/**
 * Method matcher
 *
 * @test  xp://inject.unittest.aop.MethodsTest
 */
class Methods extends \lang\Object {
  protected $match;

  /** @param function(lang.reflect.Routine): bool $match */
  protected function __construct($match) { $this->match= $match; }

  /**
   * Returns all methods
   *
   * @return self
   */
  public static function all() {
    return new self(function($routine) { return true; });
  }

  /**
   * Returns all methods annotated with a given annotation 
   *
   * @param  string $annotation
   * @return self
   */
  public static function annotatedWith($annotation) {
    return new self(function($routine) use($annotation) {
      return $routine->hasAnnotation($annotation);
    });
  }

  /**
   * Returns all methods returning a given type
   *
   * @param  var $type Either a lang.Type instance or a string
   * @return self
   */
  public static function returning($type) {
    $t= $type instanceof Type ? $type : Type::forName($type);
    return new self(function($routine) use($t) {
      $returns= $routine->getReturnType();
      return $t->equals($returns) || $t->isAssignableFrom($returns);
    });
  }

  /**
   * Returns all methods named by a given pattern. If the pattern contains
   * the asterisk (`*`) prefix-matching will be performed. For example, the
   * pattern `set*` matches all methods beginning with "set"). Otherwise,
   * the given pattern is compared against the complete name.
   *
   * @param  string $pattern
   * @return self
   */
  public static function named($pattern) {
    if ('*' === $pattern) {
      return new self(function($routine) { return true; });
    } else if (false === ($pos= strpos($pattern, '*'))) {
      return new self(function($routine) use($pattern) {
        return $routine->getName() === $pattern;
      });
    } else {
      return new self(function($routine) use($pattern, $pos) {
        return 0 === substr_compare($routine->getName(), $pattern, 0, $pos);
      });
    }
  }

  /**
   * Returns all methods where all given conditions match
   *
   * @param  self[] $conditions
   * @return self
   */
  public static function allOf(array $conditions) {
    return new self(function($routine) use($conditions) {
      foreach ($conditions as $methods) {
        if (!$methods->match($routine)) return false;
      }
      return true;
    });
  }

  /**
   * Returns all methods where at least one of the given conditions match
   *
   * @param  self[] $conditions
   * @return self
   */
  public static function anyOf(array $conditions) {
    return new self(function($routine) use($conditions) {
      foreach ($conditions as $methods) {
        if ($methods->match($routine)) return true;
      }
      return false;
    });
  }

  /**
   * Returns whether this matcher matches the given routine 
   *
   * @param  lang.reflect.Routine $routine
   * @return bool
   */
  public function match($routine) {
    $f= $this->match;
    return $f($routine);
  }
}