<?php namespace inject;

use lang\ClassLoader;

class Proxy extends \lang\Object {
  protected static $uniq= 0;

  /**
   * Creates a new proxy for a given type
   *
   * @param  lang.XPClass $type
   */
  public function __construct($type) {
    $decl= '{ public static $__intercept; ';
    foreach ($type->getDeclaredMethods() as $method) {
      $decl.= $this->declarationOf($method);
    }
    $decl.= '}';

    $this->class= ClassLoader::defineClass($type->getName().'Proxy'.(self::$uniq++), $type, [], $decl);
  }

  /**
   * Returns the declaration for a given routine
   *
   * @param  lang.reflect.Routine $routine
   * @return string
   */
  protected function declarationOf($routine) {
    $args= $signature= '';
    foreach ($routine->getParameters() as $i => $param) {
      $args.= ', $__a'.$i;

      if ($restrict= $param->getTypeRestriction()) {
        $signature.= ', '.$restrict.' $__a'.$i;
      } else {
        $signature.= ', $__a'.$i;
      }

      if ($param->isOptional()) {
        $signature.= '= '.var_export($param->getDefaultValue(), true);
      }
    }

    return 'function '.$routine->getName().'('.substr($signature, 2).') {
      $invocation= new \inject\MethodInvocation(
        $this,
        \''.$routine->getName().'\',
        ['.substr($args, 2).']
      );
      self::$__intercept->invoke($invocation);
      if ($invocation->proceed) {
        return parent::'.$routine->getName().'('.substr($args, 2).');
      }
    }';
  }

  /**
   * Returns the class with the given interception installed
   *
   * @param  inject.MethodInterception $interception
   * @return lang.XPClass
   */
  public function withInterception($interception) {
    $this->class->getField('__intercept')->set(null, $interception);
    return $this->class;
  }
}