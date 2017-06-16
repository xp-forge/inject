<?php namespace inject;

use lang\Type;
use lang\XPClass;
use lang\TypeUnion;
use lang\Primitive;
use lang\Throwable;
use lang\IllegalArgumentException;
use lang\reflect\TargetInvocationException;

/**
 * Injector
 *
 * @test    xp://inject.unittest.InjectorTest
 */
class Injector {
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
  public function __construct(... $initial) {
    $this->bind(typeof($this), $this);
    foreach ($initial as $bindings) {
      $this->add($bindings);
    }
  }

  /**
   * Returns a binding
   *
   * @param  lang.Type $t
   * @param  var $impl
   */
  public static function asBinding($t, $impl) {
    if ($impl instanceof XPClass) {
      return new ClassBinding($impl, $t);
    } else if (self::$PROVIDER->isInstance($impl) || $impl instanceof Provider) {
      return new ProviderBinding($impl);
    } else if (is_object($impl)) {
      return new InstanceBinding($impl, $t);
    } else if (is_array($impl)) {
      return new ArrayBinding($impl, $t);
    } else {
      return new ClassBinding(XPClass::forName((string)$impl), $t);
    }
  }

  /**
   * Add bindings
   *
   * @param  inject.Bindings $bindings
   * @return self
   */
  public function add(Bindings $bindings) {
    $bindings->configure($this);
    return $this;
  }

  /**
   * Add a binding
   *
   * @param  string|lang.Type $type
   * @param  var $impl
   * @param  string $name
   * @return self
   * @throws lang.IllegalArgumentException
   */
  public function bind($type, $impl, $name= null) {
    $t= $type instanceof Type ? $type : Type::forName($type);

    if ($impl instanceof Named) {
      $this->bindings[$t->literal()]= $impl;
    } else if ($t instanceof Primitive) {
      if (null === $name) {
        throw new IllegalArgumentException('Cannot bind primitive type '.$t.' without a name');
      }
      $this->bindings[$t->literal()][$name]= new InstanceBinding($impl, $t);
    } else {
      $this->bindings[$t->literal()][$name]= self::asBinding($t, $impl);
    }
    return $this;
  }

  /**
   * Get a binding
   *
   * @param  string|lang.Type $type
   * @param  string $name
   * @return var or NULL if none exists
   */
  public function get($type, $name= null) {
    $t= $type instanceof Type ? $type : Type::forName($type);

    if ($t instanceof TypeUnion) {
      foreach ($t->types() as $type) {
        if ($instance= $this->get($type, $name)) return $instance;
      }
    } else if (self::$PROVIDER->isAssignableFrom($t)) {
      $literal= $t->genericArguments()[0]->literal();
      if (isset($this->bindings[$literal][$name])) {
        return $this->bindings[$literal][$name]->provider($this);
      }
    } else {
      $literal= $t->literal();
      if (isset($this->bindings[$literal][$name])) {
        return $this->bindings[$literal][$name]->resolve($this);
      } else if (null === $name && $t instanceof XPClass && !($t->isInterface() || $t->getModifiers() & MODIFIER_ABSTRACT)) {
        return $this->newInstance($t);
      }
    }

    return null;
  }

  /**
   * Retrieve bound value for injection
   *
   * @param  var $inject The annotation
   * @param  lang.reflect.Routine $routine
   * @param  lang.reflect.Parameter $param
   * @return var
   * @throws inject.ProvisionException
   */
  private function param($inject, $routine, $param) {
    $binding= $this->get(
      isset($inject['type']) ? $inject['type'] : ($param->getTypeRestriction() ?: $param->getType()),
      isset($inject['name']) ? $inject['name'] : null
    );

    if (null === $binding) {
      if ($param->isOptional()) {
        return $param->getDefaultValue();
      } else {
        throw new ProvisionException(sprintf(
          'Unknown injection type %s%s for %s\'s %s() parameter %s',
          isset($inject['type']) ? $inject['type'] : $param->getTypeName(),
          isset($inject['name']) ? ' named "'.$inject['name'].'"' : '',
          $routine->getDeclaringClass()->getName(),
          $routine->getName(),
          $param->getName()
        ));
      }
    }
    return $binding;
  }

  /**
   * Retrieve args for a given routine
   *
   * @param  lang.reflect.Routine $routine
   * @param  [:var] $named Named arguments
   * @return var
   * @throws inject.ProvisionException
   */
  protected function args($routine, $named) {
    $inject= $routine->hasAnnotation('inject');
    $args= [];
    foreach ($routine->getParameters() as $i => $param) {
      $name= $param->getName();
      if (isset($named[$name])) {
        $args[]= $named[$name];
      } else if ($param->hasAnnotation('inject')) {
        $args[]= $this->param($param->getAnnotation('inject'), $routine, $param);
      } else if ($inject) {
        $args[]= $this->param(0 === $i ? $routine->getAnnotation('inject') : [], $routine, $param);
      } else if (!$param->isOptional()) {
        throw new ProvisionException(sprintf(
          'Value required for %s\'s %s() parameter %s',
          $routine->getDeclaringClass()->getName(),
          $routine->getName(),
          $name
        ));
      }
    }
    return $args;
  }

  /**
   * Creates a new instance of a given class. If the constructor uses
   * injection, the arguments are compiled from the relevant annotations.
   * Otherwise, optional constructor arguments may be passed.
   *
   * @param   lang.XPClass $class
   * @param   [:var] $named Named arguments
   * @return  var
   * @throws  inject.ProvisionException
   */
  public function newInstance(XPClass $class, $named= []) {
    if ($class->hasConstructor()) {
      $constructor= $class->getConstructor();
      try {
        return $constructor->newInstance($this->args($constructor, $named));
      } catch (TargetInvocationException $e) {
        throw new ProvisionException('Error creating an instance of '.$class->getName(), $e->getCause());
      } catch (Throwable $e) {
        throw new ProvisionException('Error creating an instance of '.$class->getName(), $e);
      }
    } else {
      return $class->newInstance();
    }
  }
}