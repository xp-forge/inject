<?php namespace inject;

use lang\reflect\TargetInvocationException;
use lang\{IllegalArgumentException, Nullable, Primitive, Throwable, Type, TypeUnion, XPClass};

/**
 * Injector
 *
 * @test    xp://inject.unittest.InjectorTest
 */
class Injector {
  protected static $PROVIDER;
  protected $bindings= [];
  protected $protect= [];

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
      $bindings->configure($this);
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
   * Bind to a given type
   *
   * @param  string|lang.Type $type
   * @param  var $impl
   * @param  ?string $name
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
      $this->bindings[$t->literal()][$name]= $impl instanceof Binding ? $impl : new InstanceBinding($impl, $t);
    } else {
      $this->bindings[$t->literal()][$name]= $impl instanceof Binding ? $impl : self::asBinding($t, $impl);
    }

    return $this;
  }

  /**
   * Returns the lookup if it provides a value, null otherwise
   *
   * @param  inject.Binding $lookup
   * @return ?inject.Binding
   */
  private function provided($lookup) {
    return $lookup === Bindings::$ABSENT || $lookup instanceof ProvisionException ? null : $lookup;
  }

  /**
   * Retrieve arguments for a given routine
   *
   * @param  lang.reflect.Routine $routine
   * @param  [:var] $named
   * @return var[]|inject.ProvisionException
   */
  private function argumentsOf($routine, $named= []) {
    $args= [];
    foreach ($routine->getParameters() as $i => $param) {
      $name= $param->getName();
      if (isset($named[$name])) {
        $args[]= $named[$name];
        continue;
      }

      if ($param->hasAnnotation('inject')) {
        $inject= $param->getAnnotation('inject');
      } else if (0 === $i) {
        $inject= $routine->hasAnnotation('inject') ? $routine->getAnnotation('inject') : null;
      } else {
        $inject= null;
      }

      if (is_array($inject)) {
        $type= isset($inject['type']) ? Type::forName($inject['type']) : ($param->getTypeRestriction() ?: $param->getType());
        $lookup= $this->binding($type, $inject['name'] ?? null);
      } else {
        $type= $param->getTypeRestriction() ?: $param->getType();
        $lookup= $this->binding($type, $inject);
      }

      if ($binding= $this->provided($lookup) ?? $this->provided($this->binding($type, $name))) {
        $args[]= $binding->resolve($this);
      } else if ($param->isOptional()) {
        $args[]= $param->getDefaultValue();
      } else {
        return new ProvisionException(sprintf(
          'No bound value for type %s%s in %s\'s %s() parameter %s',
          $type->getName(),
          isset($inject['name']) ? ' named "'.$inject['name'].'"' : '',
          $routine->getDeclaringClass()->getName(),
          $routine->getName(),
          $param->getName()
        ));
      }
    }

    return $args;
  }

  /**
   * Implicitely creates an instance binding for a given class.
   *
   * @param  lang.XPClass $class
   * @param  [:var] $named
   * @return inject.Binding
   */
  private function instanceOf($class, $named= []) {
    if (!$class->hasConstructor()) return new InstanceBinding($class->newInstance());

    $constructor= $class->getConstructor();
    $arguments= $this->argumentsOf($constructor, $named);
    if ($arguments instanceof ProvisionException) return $arguments;

    return new InstanceBinding($constructor->newInstance($arguments));
  }

  /**
   * Looks up a binding.
   *
   * @param  string|lang.Type $type
   * @param  ?string $name
   * @return inject.Binding
   */
  public function binding($type, $name= null) {
    $t= $type instanceof Type ? $type : Type::forName($type);

    // Prevent lookup loops, see https://github.com/xp-forge/inject/issues/24
    $key= $t->getName().'@'.$name;
    if (isset($this->protect[$key])) return new ProvisionException('Lookup loop for '.$key);

    try {
      $this->protect[$key]= true;
      if ($t instanceof TypeUnion) {
        foreach ($t->types() as $t) {
          if ($lookup= $this->provided($this->binding($t, $name))) return $lookup;
        }
      } else if ($t instanceof Nullable) {
        return $this->binding($t->underlyingType(), $name);
      } else {
        $literal= $t->literal();
        if (isset($this->bindings[$literal][$name])) {
          return $this->bindings[$literal][$name];
        } else if (null === $name && $t instanceof XPClass && !($t->isInterface() || $t->getModifiers() & MODIFIER_ABSTRACT)) {
          return $this->instanceOf($t);
        }
      }

      return Bindings::$ABSENT;
    } finally {
      unset($this->protect[$key]);
    }
  }

  /**
   * Get a binding's value. Returns null if no binding can be found.
   *
   * @param  string|lang.Type $type
   * @param  ?string $name
   * @return var
   * @throws inject.ProvisionException
   */
  public function get($type, $name= null) {
    $t= $type instanceof Type ? $type : Type::forName($type);

    // BC, use $inject->binding($type)->provider() instead!
    if (self::$PROVIDER->isAssignableFrom($t)) {
      $literal= $t->genericArguments()[0]->literal();
      if (isset($this->bindings[$literal][$name])) {
        return $this->bindings[$literal][$name]->provider($this);
      }
    }

    return $this->binding($t, $name)->resolve($this);
  }

  /**
   * Retrieve args for a given routine.
   *
   * @param  lang.reflect.Routine $routine
   * @param  [:var] $named Named arguments
   * @return var[]
   * @throws inject.ProvisionException
   */
  public function args($routine, $named= []) {
    return $this->argumentsOf($routine, $named)->resolve($this);
  }

  /**
   * Creates a new instance of a given class. If the constructor uses
   * injection, the arguments are compiled from the relevant annotations.
   * Otherwise, optional constructor arguments may be passed.
   *
   * @param   lang.XPClass $class
   * @param   [:var] $named Named arguments
   * @return  object
   * @throws  inject.ProvisionException
   */
  public function newInstance(XPClass $class, $named= []) {
    try {
      return $this->instanceOf($class, $named)->resolve($this);
    } catch (TargetInvocationException $e) {
      throw new ProvisionException('Error creating an instance of '.$class->getName(), $e->getCause());
    } catch (Throwable $e) {
      throw new ProvisionException('Error creating an instance of '.$class->getName(), $e);
    }
  }
}