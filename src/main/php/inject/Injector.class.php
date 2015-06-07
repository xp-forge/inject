<?php namespace inject;

use lang\Type;
use lang\XPClass;
use lang\Generic;
use lang\Throwable;
use lang\IllegalArgumentException;
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
    $this->bind($this->getClass(), $this);
    foreach (func_get_args() as $bindings) {
      $this->add($bindings);
    }
  }

  /**
   * Returns a binding
   *
   * @param  lang.XPClass $t
   * @param  var $impl
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
      }
    } else {
      if (isset($this->bindings[$combined= $t->literal().$name])) {
        return $this->bindings[$combined]->resolve($this);
      } else if ($t instanceof XPClass && !($t->isInterface() || $t->getModifiers() & MODIFIER_ABSTRACT)) {
        return $this->newInstance($t);
      }
    }
    return null;
  }

  /**
   * Retrieve bound value for injection
   *
   * @param  var $inject The annotation
   * @param  lang.reflect.Field $field
   * @return var
   * @throws inject.ProvisionException
   */
  private function field($inject, $field) {
    $binding= $this->get(
      isset($inject['type']) ? $inject['type'] : $field->type(),
      isset($inject['name']) ? $inject['name'] : null
    );

    if (null === $binding) {
      throw new ProvisionException(sprintf(
        'Unknown injection type %s%s for %s\'s field %s',
        $type,
        isset($inject['name']) ? ' named "'.$inject['name'].'"' : '',
        $field->declaredIn()->name(),
        $field->name()
      ));
    }
    return $binding;
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
      isset($inject['type']) ? $inject['type'] : $param->type(),
      isset($inject['name']) ? $inject['name'] : null
    );

    if (null === $binding) {
      if ($param->isOptional()) {
        return $param->defaultValue();
      } else {
        throw new ProvisionException(sprintf(
          'Unknown injection type %s%s for %s\'s %s() parameter %s',
          $type,
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
   * @param  bool $target
   * @return var
   * @throws inject.ProvisionException
   */
  protected function args($routine, $named, $target) {
    $inject= $routine->annotations()->present('inject');
    $args= [];
    foreach ($routine->parameters() as $i => $param) {
      $name= $param->name();
      if (isset($named[$name])) {
        $args[]= $named[$name];
      } else if ($param->annotations()->present('inject')) {
        $target= true;
        $args[]= $this->param($param->annotations()->named('inject')->value(), $routine, $param);
      } else if ($inject) {
        $target= true;
        $args[]= $this->param(0 === $i ? $routine->annotations()->named('inject')->value() : [], $routine, $param);
      } else if ($target && !$param->isOptional()) {
        throw new ProvisionException(sprintf(
          'Value required for %s\'s %s() parameter %s',
          $routine->getDeclaringClass()->getName(),
          $routine->name(),
          $name
        ));
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
   * @param   [:var] $named Named arguments
   * @return  lang.Generic
   * @throws  inject.ProvisionException
   */
  public function newInstance(XPClass $class, $named= []) {
    $mirror= new TypeMirror($class);
    $constructor= $mirror->constructor();

    try {
      return $constructor->newInstance(...$this->args($constructor, $named, true));
    } catch (TargetInvocationException $e) {
      throw new ProvisionException('Error creating an instance of '.$mirror->name(), $e->getCause());
    } catch (Throwable $e) {
      throw new ProvisionException('Error creating an instance of '.$mirror->name(), $e);
    }
  }

  /**
   * Inject members of an instance
   *
   * @param   lang.Generic $instance
   * @return  lang.Generic
   * @throws  inject.ProvisionException
   */
  public function into(Generic $instance) {
    $mirror= new TypeMirror($instance->getClass());

    foreach ($mirror->fields() as $field) {
      if (!$field->annotations()->present('inject')) continue;
      try {
        $field->modify($instance, $this->field($field->annotations()->named('inject')->value(), $field));
      } catch (Throwable $e) {
        throw new ProvisionException('Error setting '.$mirror->name().'::$'.$field->name());
      }
    }

    foreach ($mirror->methods() as $method) {
      if (null === ($args= $this->args($method, [], false))) continue;
      try {
        $method->invoke($instance, $args);
      } catch (TargetInvocationException $e) {
        throw new ProvisionException('Error invoking '.$mirror->name().'::'.$method->name(), $e->getCause());
      } catch (Throwable $e) {
        throw new ProvisionException('Error invoking '.$mirror->name().'::'.$method->name(), $e);
      }
    }

    return $instance;
  }
}
