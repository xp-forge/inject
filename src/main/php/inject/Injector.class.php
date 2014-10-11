<?php namespace inject;

use lang\Type;
use lang\XPClass;
use lang\Generic;
use lang\Throwable;
use lang\IllegalArgumentException;
use lang\reflect\TargetInvocationException;

/**
 * Injector
 *
 * @test    xp://inject.unittest.InjectorTest
 */
class Injector extends \lang\Object {
  protected static $PROVIDER;
  protected $bindings= [];

  static function __static() {
    self::$PROVIDER= Type::forName('inject.Provider<?>');
  }

  /**
   * Creates a new injector optionally given initial bindings
   *
   * @param  inject.Bindings... $initial
   */
  public function __construct() {
    foreach (func_get_args() as $bindings) {
      $bindings->bind($this);
    }
    $this->bind($this->getClass(), $this);
  }

  /**
   * Returns a binding
   *
   * @param  lang.XPClass $t
   * @param  lang.XPClass $impl
   */
  protected function asBinding($t, $impl) {
    if ($impl instanceof XPClass) {
      return new ClassBinding($t, $impl);
    } else if (self::$PROVIDER->isInstance($impl) || $impl instanceof Provider) {
      return new ProviderBinding($t, $impl);
    } else if ($impl instanceof Generic) {
      return new InstanceBinding($t, $impl);
    } else {
      return new ClassBinding($t, XPClass::forName((string)$impl));
    }
  }

  /**
   * Add a binding
   *
   * @param  var $type either a lang.Type instance or a type name
   * @param  var $impl
   * @param  string $name
   * @return self
   * @throws lang.IllegalArgumentException
   */
  public function bind($type, $impl, $name= null) {
    $t= $type instanceof Type ? $type : Type::forName($type);

    if ($t instanceof XPClass) {
      $this->bindings[$t->literal().$name]= $this->asBinding($t, $impl);
    } else if (null === $name) {
      throw new IllegalArgumentException('Cannot bind non-class type '.$t.' without a name');
    } else {
      $this->bindings[$t->literal().$name]= new InstanceBinding($t, $impl);
    }

    return $this;
  }

  /**
   * Get a binding
   *
   * @param  var $type either a lang.Type instance or a type name
   * @param  string $name
   * @return var or NULL if none exists
   */
  public function get($type, $name= null) {
    $t= $type instanceof Type ? $type : Type::forName($type);

    if (self::$PROVIDER->isAssignableFrom($t)) {
      if (isset($this->bindings[$combined= $t->genericArguments()[0]->literal().$name])) {
        return $this->bindings[$combined]->provider($this);
      } else {
        return null;
      }
    } else if (isset($this->bindings[$combined= $t->literal().$name])) {
      return $this->bindings[$combined]->resolve($this);
    } else if ($t instanceof XPClass && !($t->isInterface() || $t->getModifiers() & MODIFIER_ABSTRACT)) {
      return $this->newInstance($t);
    } else {
      return null;
    }
  }

  /**
   * Retrieve bound value for injection
   *
   * @param  var $inject The annotation
   * @param  lang.Type $type
   * @return var
   * @throws inject.ProvisionException
   */
  protected function bound($inject, $type) {
    $binding= $this->get(
      isset($inject['type']) ? $inject['type'] : $type,
      isset($inject['name']) ? $inject['name'] : null
    );
    if (null === $binding) {
      throw new ProvisionException(sprintf(
        'Unknown injection type %s%s',
        $type,
        isset($inject['name']) ? 'named "'.$inject['name'].'"' : ''
      ));
    }
    return $binding;
  }

  /**
   * Retrieve args for a given routine
   *
   * @param  lang.reflect.Routine $routine
   * @param  var[] $default The default arguments
   * @param  bool $target
   * @return var
   * @throws inject.ProvisionException
   */
  protected function args($routine, $default, $target) {
    $inject= $routine->hasAnnotation('inject');

    $args= [];
    foreach ($routine->getParameters() as $i => $param) {
      if ($inject && 0 === $i) {
        $target= true;
        $args[]= $this->bound($routine->getAnnotation('inject'), $param->getTypeRestriction() ?: $param->getType());
      } else if ($param->hasAnnotation('inject')) {
        $target= true;
        $args[]= $this->bound($param->getAnnotation('inject'), $param->getTypeRestriction() ?: $param->getType());
      } else if (array_key_exists($i, $default)) {
        $args[]= $default[$i];
      } else if ($target && !$param->isOptional()) {
        throw new ProvisionException(sprintf(
          'Value required for %s\'s %s() parameter %s',
          $routine->getDeclaringClass()->getName(),
          $routine->getName(),
          $param->getName()
        ));
      } else {
        break;
      }
    }
    return $target ? $args : null;
  }

  /**
   * Creates a new instance of a given class. If the constructor uses
   * injection, the arguments are compiled from the relevant annotations.
   * Otherwise, optional constructor arguments may be passed.
   *
   * @param   lang.XPClass $class
   * @param   var[] $args
   * @return  lang.Generic
   * @throws  inject.ProvisionException
   */
  public function newInstance(XPClass $class, $args= []) {
    if ($class->hasConstructor()) {
      $constructor= $class->getConstructor();
      try {
        $instance= $constructor->newInstance($this->args($constructor, $args, true));
      } catch (TargetInvocationException $e) {
        throw new ProvisionException('Error creating an instance of '.$class->getName().': '.$e->getCause()->getMessage(), $e->getCause());
      } catch (Throwable $e) {
        throw new ProvisionException('Error creating an instance of '.$class->getName().': '.$e->getMessage(), $e);
      }
    } else {
      $instance= $class->newInstance();
    }

    return $this->injectInto($instance);
  }

  /**
   * Inject members of an instance
   *
   * @param   lang.Generic $instance
   * @return  lang.Generic
   * @throws  inject.ProvisionException
   */
  public function injectInto(Generic $instance) {
    $class= $instance->getClass();

    foreach ($class->getFields() as $field) {
      if (!$field->hasAnnotation('inject')) continue;
      try {
        $field->set($instance, $this->bound($field->getAnnotation('inject'), $field->getType()));
      } catch (Throwable $e) {
        throw new ProvisionException('Error setting '.$class->getName().'::$'.$field->getName().': '.$e->getMessage());
      }
    }

    foreach ($class->getMethods() as $method) {
      if (null === ($args= $this->args($method, [], false))) continue;
      try {
        $method->invoke($instance, $args);
      } catch (TargetInvocationException $e) {
        throw new ProvisionException('Error invoking '.$class->getName().'::'.$method->getName().': '.$e->getCause()->getMessage(), $e->getCause());
      } catch (Throwable $e) {
        throw new ProvisionException('Error invoking '.$class->getName().'::'.$method->getName().': '.$e->getMessage(), $e);
      }
    }

    return $instance;
  }
}
