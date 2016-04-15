<?php namespace inject;

use util\PropertyAccess;
use util\Properties;
use lang\XPClass;
use lang\Type;

/**
 * Bindings from a properties file
 *
 * ```ini
 * scriptlet.Session=com.example.session.FileSystem
 * scriptlet.Session=com.example.session.MemCache("tcp://localhost:11211")
 * ```
 *
 * @test    xp://inject.unittest.ConfiguredBindingsTest
 */
class ConfiguredBindings extends Bindings {
  private $properties;
  private static $PRIMITIVES= [
    'string' => true,
    'int'    => true,
    'double' => true,
    'bool'   => true
  ];

  /** @param util.PropertyAccess|string */
  public function __construct($arg) {
    if ($arg instanceof PropertyAccess) {
      $this->properties= $arg;
    } else {
      $this->properties= new Properties($arg);
    }
  }

  /**
   * Converts a given literal to a value
   *
   * @param  string $literal
   * @return var
   */
  private function valueIn($literal) {
    if (0 === strncasecmp($literal, 'true', 4)) {
      return true;
    } else if (0 === strncasecmp($literal, 'false', 5)) {
      return false;
    } else if (0 === strncasecmp($literal, 'null', 4)) {
      return null;
    } else if (is_numeric($literal)) {
      return strstr($literal, '.') ? (double)$literal : (int)$literal;
    } else {
      return $literal;
    }
  }

  /**
   * Parse arguments from a string. Supports strings, booleans, null, and numbers.
   *
   * @param  string $input
   * @return var[]
   */
  private function argumentsIn($input) {
    if ('' === $input) return [];

    $arguments= [];
    for ($o= 0, $l= strlen($input); $o < $l; $o+= $p) {
      if ('"' === $input{$o} || "'" === $input{$o}) {
        $s= $o + 1;
        $str= '';
        do {
          $p= strcspn($input, $input{$o}, $s);
          if ('\\' === $input{$s + $p - 1}) {
            $str.= substr($input, $s, $p - 1).$input{$o};
            $s+= $p + 1;
            continue;
          } else {
            $str.= substr($input, $s, $p);
            $s+= $p + 1;
            break;
          }
        } while ($s < $l);
        $arguments[]= $str;
        $p+= $s;
      } else {
        $p= strcspn($input, ',', $o);
        $arguments[]= $this->valueIn(substr($input, $o, $p));
      }
    }
    return $arguments;
  }

  /**
   * Resolves a name
   *
   * @param  string $namespace
   * @param  string $name
   * @return lang.XPClass
   */
  private function resolveType($namespace, $name) {
    return XPClass::forName(strstr($name, '.') ? $name : $namespace.'.'.$name);
  }

  /**
   * Parse implementation from a string.
   *
   * @param  string $namespace
   * @param  string $input
   * @return var
   */
  private function bindingTo($namespace, $input) {
    if (false === ($p= strpos($input, '('))) {
      return $this->resolveType($namespace, $input);
    } else {
      $class= $this->resolveType($namespace, substr($input, 0, $p));
      if ($class->hasConstructor()) {
        $arguments= $this->argumentsIn(substr($input, $p + 1, -1));
        return $class->getConstructor()->newInstance($arguments);
      } else {
        return $class->newInstance();
      }
    }
  }

  /**
   * Configures bindings on given injector
   *
   * @param  inject.Injector $injector
   */
  public function configure($injector) {
    $namespace= $this->properties->getFirstSection();
    do {
      foreach ($this->properties->readSection($namespace) as $type => $implementation) {
        if (isset(self::$PRIMITIVES[$type])) {
          foreach ($implementation as $name => $value) {
            $injector->bind($type, $this->valueIn($value), $name);
          }
        } else {
          $resolved= $this->resolveType($namespace, $type);
          if (is_array($implementation)) {
            foreach ($implementation as $name => $impl) {
              $injector->bind($resolved, $this->bindingTo($namespace, $impl), $name);
            }
          } else {
            $injector->bind($resolved, $this->bindingTo($namespace, $implementation));
          }
        }
      }
    } while ($namespace= $this->properties->getNextSection());
  }
}