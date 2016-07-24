<?php namespace inject;

use lang\Type;
use lang\XPClass;
use lang\TypeUnion;
use lang\Primitive;
use lang\Throwable;
use lang\IllegalArgumentException;
use lang\IllegalAccessException;
use lang\mirrors\TypeMirror;
use lang\mirrors\TargetInvocationException;

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
    $this->bind(typeof($this), $this);
    foreach (func_get_args() as $bindings) {
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
   * @param  lang.mirrors.Routine $routine
   * @param  lang.mirrors.Parameter $param
   * @return var
   * @throws inject.ProvisionException
   */
  private function param($inject, $routine, $param) {
    $binding= $this->get(
      isset($inject['type']) ? $inject['type'] : $param->type(),
      isset($inject['name']) ? $inject['name'] : null
    );

    if (null === $binding) {
      if ($param->isOptional()) {
        return $param->defaultValue();
      } else {
        throw new ProvisionException(sprintf(
          'Unknown injection type %s%s for %s\'s %s() parameter %s',
          isset($inject['type']) ? $inject['type'] : $param->type()->getName(),
          isset($inject['name']) ? ' named "'.$inject['name'].'"' : '',
          $routine->declaredIn()->name(),
          $routine->name(),
          $param->name()
        ));
      }
    }
    return $binding;
  }

  /**
   * Retrieve args for a given routine
   *
   * @param  lang.mirrors.Routine $routine
   * @param  [:var] $named Named arguments
   * @return php.Generator
   * @throws inject.ProvisionException
   */
  private function args($routine, $named) {
    $inject= $routine->annotations()->present('inject');
    foreach ($routine->parameters() as $i => $param) {
      $name= $param->name();
      if (isset($named[$name])) {
        yield $named[$name];
      } else if ($param->annotations()->present('inject')) {
        $target= true;
        yield $this->param($param->annotations()->named('inject')->value(), $routine, $param);
      } else if ($inject) {
        $target= true;
        yield $this->param(0 === $i ? $routine->annotations()->named('inject')->value() : [], $routine, $param);
      } else if (!$param->isOptional()) {
        throw new ProvisionException(sprintf(
          'Value required for %s\'s %s() parameter %s',
          $routine->declaredIn()->name(),
          $routine->name(),
          $name
        ));
      }
    }
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
    $mirror= new TypeMirror($class);
    $constructor= $mirror->constructor();
    $modifiers= $constructor->modifiers();

    if (!$modifiers->isPublic()) {
      throw new ProvisionException(
        'Error creating an instance of '.$mirror->name(),
        new IllegalAccessException('Cannot invoke '.$modifiers->names().' constructor')
      );
    }

    try {
      return $constructor->newInstance(...$this->args($constructor, $named));
    } catch (TargetInvocationException $e) {
      throw new ProvisionException('Error creating an instance of '.$mirror->name(), $e->getCause());
    } catch (Throwable $e) {
      throw new ProvisionException('Error creating an instance of '.$mirror->name(), $e);
    }
  }
}