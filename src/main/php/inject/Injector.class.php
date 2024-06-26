<?php namespace inject;

use lang\{IllegalArgumentException, Nullable, Primitive, Throwable, Type, TypeUnion, XPClass, Reflection};

/**
 * Injector
 *
 * @test  inject.unittest.InjectorTest
 * @test  inject.unittest.BindingTest
 * @test  inject.unittest.BindingsTest
 * @test  inject.unittest.ProvidersTest
 * @test  inject.unittest.NewInstanceTest
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
      return new ClassBinding(Reflection::type((string)$impl), $t);
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
   * @param  lang.reflection.Routine $routine
   * @param  [:var] $named
   * @return inject.Binding
   */
  private function argumentsOf($routine, $named= []) {
    $args= [];
    foreach ($routine->parameters() as $name => $param) {
      if (isset($named[$name])) {
        $args[]= $named[$name];
        continue;
      }

      if ($annotation= $param->annotation(Inject::class)) {
        $inject= $annotation->arguments();
      } else if (0 === $param->position() && $annotation= $routine->annotation(Inject::class)) {
        $inject= $annotation->arguments();
      } else {
        $inject= [];
      }

      $type= isset($inject['type']) ? Type::forName($inject['type']) : $param->constraint()->type();
      $lookup= $this->binding($type, $inject['name'] ?? $inject[0] ?? null);

      if ($binding= $this->provided($lookup) ?? $this->provided($this->binding($type, $name))) {
        $args[]= $binding->resolve($this);
      } else if ($param->optional()) {
        $args[]= $param->default();
      } else {
        return new ProvisionException(sprintf(
          'No bound value for type %s%s in %s\'s %s() parameter %s',
          $type->getName(),
          isset($inject['name']) ? ' named "'.$inject['name'].'"' : '',
          $routine->declaredIn()->name(),
          $routine->name(),
          $name
        ));
      }
    }

    return new InstanceBinding($args);
  }

  /**
   * Implicitely creates an instance binding for a given class.
   *
   * @param  lang.reflection.Type $type
   * @param  [:var] $named
   * @return inject.Binding
   * @throws inject.ProvisionException
   */
  private function instanceOf($type, $named= []) {
    if (!$type->instantiable(true)) {
      throw new ProvisionException('Cannot instantiate '.$type->name().' with non-public constructor');
    }

    $constructor= $type->constructor();
    if (null === $constructor) return new InstanceBinding($type->newInstance());

    $arguments= $this->argumentsOf($constructor, $named);
    if (!$this->provided($arguments)) return $arguments;

    // Wrap any exception caught during instance creation. These errors
    // should not be simply returned as we risk them being overlooked!
    try {
      return new InstanceBinding($constructor->newInstance($arguments->resolve($this)));
    } catch (Throwable $e) {
      throw new ProvisionException('Error creating an instance of '.$type->name(), $e);
    }
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
      } else if (self::$PROVIDER->isAssignableFrom($t)) {
        $literal= $t->genericArguments()[0]->literal();
        if ($binding= $this->bindings[$literal][$name] ?? null) {
          return new InstanceBinding($binding->provider($this));
        }
      } else {
        $literal= $t->literal();
        if ($binding= $this->bindings[$literal][$name] ?? null) {
          return $binding;
        } else if (null === $name && $t instanceof XPClass) {
          $type= Reflection::type($t);
          if ($type->instantiable()) return $this->instanceOf($type);
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
    return $this->binding($type, $name)->resolve($this);
  }

  /**
   * Retrieve args for a given routine.
   *
   * @param  lang.reflection.Routine $routine
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
   * @param   lang.XPClass|lang.reflection.Type $class
   * @param   [:var] $named Named arguments
   * @return  object
   * @throws  inject.ProvisionException
   */
  public function newInstance($class, $named= []) {
    $type= $class instanceof XPClass ? Reflection::type($class) : $class;
    return $this->instanceOf($type, $named)->resolve($this);
  }
}