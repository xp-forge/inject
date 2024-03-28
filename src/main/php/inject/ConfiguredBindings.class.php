<?php namespace inject;

use lang\{ClassLoader, ClassNotFoundException, ClassCastException, Reflection};
use util\{Properties, PropertyAccess};

/**
 * Bindings from a properties file
 *
 * ## Basic property file
 * The bindings are written as key/value pairs. Constructor arguments
 * are denoted inside braces.
 *
 * ```ini
 * scriptlet.Session=com.example.session.FileSystem
 * scriptlet.Session=com.example.session.MemCache("tcp://localhost:11211")
 * ```
 *
 * ## Imports
 * One ore more `use` keys can be used to achieve imports.
 *
 * ```ini
 * use[]=com.example.session
 *
 * scriptlet.Session=FileSystem
 * ```
 *
 * ## Inheritance
 * If created with the optional "section" argument, bindings are created
 * from both the global section *and* the given one. The latter overwrites
 * bindings from the first and inherits ones it doesn't explicitely define.
 *
 * ```ini
 * string[name]="Sync"
 * com.example.Monitoring=com.example.monitoring.Icinga
 *
 * [add]
 * string[name]="Add users"
 * ```
 *
 * @see   https://github.com/xp-forge/inject/pull/10
 * @test  xp://inject.unittest.ConfiguredBindingsTest
 */
class ConfiguredBindings extends Bindings {
  private $properties;
  private $section= null;

  /**
   * Creates new bindings from a given properties instance.
   *
   * @param  util.PropertyAccess|string $properties
   * @param  string $section Optional section
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
   * Converts a given string to an integer
   *
   * @param  string $literal
   * @return int
   * @throws lang.ClassCastException
   */
  private function int($literal) {
    if (is_numeric($literal)) return (int)$literal;

    throw new ClassCastException('Expecting a numeric, have '.$literal);
  }

  /**
   * Converts a given string to an float
   *
   * @param  string $literal
   * @return float
   * @throws lang.ClassCastException
   */
  private function float($literal) {
    if (is_numeric($literal)) return (float)$literal;

    throw new ClassCastException('Expecting a numeric, have '.$literal);
  }

  /**
   * Converts a given string to a boolean value. Supports the strings
   * `true`, `false` as well as `1` and `0`.
   *
   * @param  string $literal
   * @return bool
   * @throws lang.ClassCastException
   */
  private function bool($literal) {
    if ('true' === $literal || '1' === $literal) return true;
    if ('false' === $literal || '0' === $literal) return false;

    throw new ClassCastException('Expecting either true or false, have '.$literal);
  }

  /**
   * Resolves a name
   *
   * @param  lang.ClassLoader $cl
   * @param  string[] $namespaces
   * @param  string $name
   * @return lang.XPClass
   * @throws lang.ClassNotFoundException
   */
  private function resolveType($cl, $namespaces, $name) {
    if (strstr($name, '.')) return $cl->loadClass($name);

    foreach ($namespaces as $namespace) {
      if ($cl->providesClass($qualified= $namespace.'.'.$name)) return $cl->loadClass($qualified);
    }
    throw new ClassNotFoundException('['.implode(', ', $namespaces).'].'.$name);
  }

  /**
   * Parse implementation from a string.
   *
   * @param  lang.ClassLoader $cl
   * @param  string[] $namespaces
   * @param  string $input
   * @return var
   */
  private function bindingTo($cl, $namespaces, $input) {
    if (false === ($p= strpos($input, '('))) {
      return $this->resolveType($cl, $namespaces, $input);
    } else {
      $type= $this->resolveType($cl, $namespaces, substr($input, 0, $p));
      $arguments= eval('return ['.substr($input, $p + 1, -1).'];');
      return Reflection::type($type)->newInstance(...$arguments);
    }
  }

  /**
   * Configures bindings on given injector
   *
   * @param  inject.Injector $injector
   */
  public function configure($injector) {
    $cl= ClassLoader::getDefault();
    foreach (array_unique([null, $this->section]) as $section) {
      $namespaces= [];
      foreach ($this->properties->readSection($section) as $type => $implementation) {
        if ('use' === $type) {
          $namespaces= $implementation;
          continue;
        }

        // Primitives: `<T>[named]=value`
        if ('string' === $type) {
          foreach ($implementation as $name => $value) $injector->bind($type, (string)$value, $name);
          continue;
        } else if ('int' === $type) {
          foreach ($implementation as $name => $value) $injector->bind($type, $this->int($value), $name);
          continue;
        } else if ('float' === $type || 'double' === $type) {
          foreach ($implementation as $name => $value) $injector->bind($type, $this->float($value), $name);
          continue;
        } else if ('bool' === $type) {
          foreach ($implementation as $name => $value) $injector->bind($type, $this->bool($value), $name);
          continue;
        }

        // Strings: `named=value`, disambiguated from `Named=Value` (type binding)
        if (false === strpos($type, '.') && $type >= 'a') {
          $injector->bind('string', (string)$implementation, $type);
          continue;
        }

        // Implementations: `package.Storage=package.FileSystem`
        // Named implementations: `package.Storage[files]=package.FileSystem`
        $resolved= $this->resolveType($cl, $namespaces, $type);
        if (is_array($implementation)) {
          foreach ($implementation as $name => $impl) {
            $injector->bind($resolved, $this->bindingTo($cl, $namespaces, $impl), $name);
          }
        } else {
          $injector->bind($resolved, $this->bindingTo($cl, $namespaces, $implementation));
        }
      }
    }
  }
}