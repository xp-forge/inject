<?php namespace inject;

use lang\{ClassLoader, ClassNotFoundException, Type};
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
  private static $PRIMITIVES= [
    'string' => true,
    'int'    => true,
    'double' => true,
    'bool'   => true
  ];

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
      $class= $this->resolveType($cl, $namespaces, substr($input, 0, $p));
      if ($class->hasConstructor()) {
        $arguments= eval('return ['.substr($input, $p + 1, -1).'];');
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
    $cl= ClassLoader::getDefault();
    foreach (array_unique([null, $this->section]) as $section) {
      $namespaces= [];
      foreach ($this->properties->readSection($section) as $type => $implementation) {
        if ('use' === $type) {
          $namespaces= $implementation;
          continue;
        }

        // Primitives: `string[named]=value`
        if (isset(self::$PRIMITIVES[$type])) {
          foreach ($implementation as $name => $value) {
            $injector->bind($type, $this->valueIn($value), $name);
          }
          continue;
        }

        // Strings: `named=value`, disambiguated from `Named=Value` (type binding)
        if (false === strpos($type, '.') && $type >= 'a') {
          $injector->bind('string', $implementation, $type);
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