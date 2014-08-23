<?php namespace inject;

use lang\Type;
use lang\XPClass;
use lang\Generic;

/**
 * Injector
 *
 * @test    xp://inject.unittest.InjectorTest
 */
class Injector extends \lang\Object {
  protected $bindings= [];

  /**
   * Add a binding
   *
   * @param   var $type either a lang.Type instance or a type name
   * @param   var $impl
   * @param   string $name
   * @return  self
   */
  public function bind($type, $impl, $name= null) {
    $key= $type instanceof Type ? $type->literal() : Type::forName($type)->literal();
    if ($impl instanceof Provider) {
      $this->bindings[$key][$name]= $impl;
    } else {
      $this->bindings[$key][$name]= new InstanceProvider($impl);
    }
    return $this;
  }
  
  /**
   * Get a binding
   *
   * @param   var $type either a lang.Type instance or a type name
   * @param   string $name
   * @return  var or NULL if none exists
   */
  public function get($type, $name= null) {
    $key= $type instanceof Type ? $type->literal() : Type::forName($type)->literal();
    if (!isset($this->bindings[$key][$name])) return null;
    
    $bound= $this->bindings[$key][$name]->get();
    if ($bound instanceof XPClass) {
      $impl= $this->newInstance($bound);
    } else {
      $impl= $bound;
    }
    return $impl;
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
   * @param  var $default Value to return if no injection was performed
   * @return var
   * @throws inject.ProvisionException
   */
  protected function args($routine, $default) {
    if ($routine->hasAnnotation('inject')) {
      if ($routine->numParameters() < 1) {
        return $default;
      } else {
        $param= $routine->getParameter(0);
        return [$this->bound($routine->getAnnotation('inject'), $param->getTypeRestriction() ?: $param->getType())];
      }
    } else {
      $args= [];
      $target= false;
      foreach ($routine->getParameters() as $param) {
        if ($param->hasAnnotation('inject')) {
          $target= true;
          $args[]= $this->bound($param->getAnnotation('inject'), $param->getTypeRestriction() ?: $param->getType());
        } else if (!$target) {
          return $default;
        } else if ($param->isOptional()) {
          break;
        } else {
          throw new ProvisionException(sprintf(
            'Value required for %s\'s %s() parameter %s',
            $routine->getDeclaringClass()->getName(),
            $routine->getName(),
            $param->getName()
          ));
        }
      }
      return $target ? $args : $default;
    }
  }

  /**
   * Creates a new instance of a given class
   *
   * @param   lang.XPClass class
   * @return  lang.Generic
   * @throws  inject.ProvisionException
   */
  public function newInstance(XPClass $class) {
    if ($class->hasConstructor()) {
      $constructor= $class->getConstructor();
      try {
        $instance= $constructor->newInstance($this->args($constructor, []));
      } catch (\lang\reflect\TargetInvocationException $e) {
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
   * @param   lang.Generic instance
   * @return  lang.Generic instance
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
      if (null === ($args= $this->args($method, null))) continue;
      try {
        $method->invoke($instance, $args);
      } catch (\lang\reflect\TargetInvocationException $e) {
        throw new ProvisionException('Error invoking '.$class->getName().'::'.$method->getName().': '.$e->getCause()->getMessage(), $e->getCause());
      } catch (Throwable $e) {
        throw new ProvisionException('Error invoking '.$class->getName().'::'.$method->getName().': '.$e->getMessage(), $e);
      }
    }

    return $instance;
  }
}
