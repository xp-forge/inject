<?php namespace inject;

use util\PropertyAccess;
use util\Properties;
use lang\ClassLoader;
use lang\ClassNotFoundException;
use lang\Type;

/**
 * Bindings from a properties file
 *
 * ```ini
 * scriptlet.Session=com.example.session.FileSystem
 * scriptlet.Session=com.example.session.MemCache("tcp://localhost:11211")
 * ```
 *
 * @see   https://github.com/xp-forge/inject/pull/10
 * @test  xp://inject.unittest.ConfiguredBindingsTest
 */
class ConfiguredBindings extends Bindings {
  private static $PRIMITIVES= [
    'string' => true,
    'int'    => true,
    'double' => true,
    'bool'   => true
  ];

  private $properties;
  private $section= null;

  /**
   * Creates new bindings from a given properties instance. If an optional
   * section identifier is given, bindings are created from both the global
   * section *and* the given one - the latter overwriting bindings from the
   * first (and inheriting ones it doesn't explicitely define).
   *
   * @param  util.PropertyAccess|string $properties
   * @param  string $section
   */
  public function __construct($properties, $section= null) {
    if ($properties instanceof PropertyAccess) {
      $this->properties= $properties;
    } else {
      $this->properties= new Properties($properties);
    }
    $this->section= $section;
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
   * @param  string[] $namespaces
   * @param  string $name
   * @return lang.XPClass
   */
  private function resolveType($namespaces, $name) {
    $cl= ClassLoader::getDefault();
    if (strstr($name, '.')) {
      return $cl->loadClass($name);
    } else {
      foreach ($namespaces as $namespace) {
        if ($cl->providesClass($qualified= $namespace.'.'.$name)) return $cl->loadClass($qualified);
      }
      throw new ClassNotFoundException('['.implode(', ', $namespaces).'].'.$name);
    }
  }

  /**
   * Parse implementation from a string.
   *
   * @param  string[] $namespaces
   * @param  string $input
   * @return var
   */
  private function bindingTo($namespaces, $input) {
    if (false === ($p= strpos($input, '('))) {
      return $this->resolveType($namespaces, $input);
    } else {
      $class= $this->resolveType($namespaces, substr($input, 0, $p));
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
    foreach (array_unique([null, $this->section]) as $section) {
      $namespaces= [];
      foreach ($this->properties->readSection($section) as $type => $implementation) {
        if ('use' === $type) {
          $namespaces= $implementation;
          continue;
        }

        if (isset(self::$PRIMITIVES[$type])) {
          foreach ($implementation as $name => $value) {
            $injector->bind($type, $this->valueIn($value), $name);
          }
        } else {
          $resolved= $this->resolveType($namespaces, $type);
          if (is_array($implementation)) {
            foreach ($implementation as $name => $impl) {
              $injector->bind($resolved, $this->bindingTo($namespaces, $impl), $name);
            }
          } else {
            $injector->bind($resolved, $this->bindingTo($namespaces, $implementation));
          }
        }
      }
    }
  }
}