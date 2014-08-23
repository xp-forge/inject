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
   * Creates a new instance of a given class
   *
   * @param   lang.XPClass class
   * @return  lang.Generic
   * @throws  inject.ProvisionException
   */
  public function newInstance(XPClass $class) {
    if ($class->hasConstructor()) {
      $constructor= $class->getConstructor();
      $args= [];
      if ($constructor->hasAnnotation('inject')) {
        $inject= $constructor->getAnnotation('inject');
        if (isset($inject['type'])) {
          $type= $inject['type'];
        } else if ($restriction= $constructor->getParameter(0)->getTypeRestriction()) {
          $type= $restriction->getName();
        } else {
          $type= $constructor->getParameter(0)->getType()->getName();
        }

        // Inject
        $binding= $this->get($type, isset($inject['name']) ? $inject['name'] : null);
        if (null === $binding) {
          throw new ProvisionException('Unknown injection type "'.$type.'" at '.$class->getName().'\'s constructor');
        }
        $args= [$binding];
      }
      try {
        $instance= $constructor->newInstance($args);
      } catch (\lang\reflect\TargetInvocationException $e) {
        throw new ProvisionException('Error creating an instance of '.$class->getName().': '.$e->getCause()->getMessage(), $e);
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

    // Fields
    foreach ($class->getFields() as $field) {
      if (!$field->hasAnnotation('inject')) continue;

      // Determine injection type
      $inject= $field->getAnnotation('inject');
      if (isset($inject['type'])) {
        $type= $inject['type'];
      } else {
        $type= $field->getType();
      }

      // Inject
      $binding= $this->get($type, isset($inject['name']) ? $inject['name'] : null);
      if (null === $binding) {
        throw new ProvisionException('Unknown injection type "'.$type.'" at field "'.$field->getName().'"');
      }

      try {
        $field->set($instance, $binding);
      } catch (Throwable $e) {
        throw new ProvisionException('Error injecting '.$type.' '.$inject['name'].': '.$e->getMessage());
      }
    }

    // Methods
    foreach ($class->getMethods() as $method) {
      if (!$method->hasAnnotation('inject')) continue;

      // Determine injection type
      $inject= $method->getAnnotation('inject');
      if (isset($inject['type'])) {
        $type= $inject['type'];
      } else if ($restriction= $method->getParameter(0)->getTypeRestriction()) {
        $type= $restriction->getName();
      } else {
        $type= $method->getParameter(0)->getType()->getName();
      }

      // Inject
      $binding= $this->get($type, isset($inject['name']) ? $inject['name'] : null);
      if (null === $binding) {
        throw new ProvisionException('Unknown injection type "'.$type.'" at method "'.$method->getName().'"');
      }

      try {
        $method->invoke($instance, array($binding));
      } catch (\lang\reflect\TargetInvocationException $e) {
        throw new ProvisionException('Error injecting '.$type.' '.$inject['name'].': '.$e->getCause()->getMessage());
      } catch (Throwable $e) {
        throw new ProvisionException('Error injecting '.$type.' '.$inject['name'].': '.$e->getMessage());
      }
    }
    return $instance;
  }
}
