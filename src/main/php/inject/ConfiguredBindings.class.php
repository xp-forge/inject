<?php namespace inject;

use util\PropertyAccess;
use util\Properties;
use lang\XPClass;

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

  /** @param util.PropertyAccess|string */
  public function __construct($arg) {
    if ($arg instanceof PropertyAccess) {
      $this->properties= $arg;
    } else {
      $this->properties= new Properties($arg);
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
    foreach (explode(',', $input) as $arg) {
      if ('"' === $arg{0} || "'" === $arg{0}) {
        $arguments[]= strtr(substr($arg, 1, -1), ['\\'.$arg{0} => $arg{0}]);
      } else if (0 === strncasecmp($arg, 'true', 4)) {
        $arguments[]= true;
      } else if (0 === strncasecmp($arg, 'false', 5)) {
        $arguments[]= false;
      } else if (0 === strncasecmp($arg, 'null', 4)) {
        $arguments[]= null;
      } else if (strstr($arg, '.')) {
        $arguments[]= (double)$arg;
      } else {
        $arguments[]= (int)$arg;
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
      foreach ($this->properties->readSection($namespace) as $name => $implementation) {
        $type= $this->resolveType($namespace, $name);
        if (is_array($implementation)) {
          foreach ($implementation as $name => $impl) {
            $injector->bind($type, $this->bindingTo($namespace, $impl), $name);
          }
        } else {
          $injector->bind($type, $this->bindingTo($namespace, $implementation));
        }
      }
    } while ($namespace= $this->properties->getNextSection());
  }
}