<?php namespace inject\aop;

use lang\XPClass;
use lang\ClassLoader;

class Proxy extends \lang\Object {
  protected $type;
  protected static $uniq= 0;

  /**
   * Creates a new proxy for a given type
   *
   * @param  lang.XPClass $type
   * @param  inject.aop.Methods $methods
   * @param  inject.aop.Interception $interception
   */
  public function __construct(XPClass $type, Methods $methods, Interception $interception) {
    $decl= '{ public static $__interception, $__routines; ';
    $routines=  [];
    foreach ($type->getDeclaredMethods() as $i => $method) {
      if ($methods->match($method)) {
        $decl.= $this->proxyMethod($i, $method);
        $routines[$i]= $method;
      }
    }
    $decl.= '}';

    $this->type= ClassLoader::defineClass($type->getName().'Proxy'.(self::$uniq++), $type, [], $decl);
    $this->type->getField('__routines')->set(null, $routines);
    $this->type->getField('__interception')->set(null, $interception);
  }

  /**
   * Returns the declaration for a given routine
   *
   * @param  int $i
   * @param  lang.reflect.Routine $routine
   * @return string
   */
  protected function proxyMethod($i, $routine) {
    $decl= new Declaration($routine);
    return $decl->withBody('
      return self::$__interception->invoke(new \inject\aop\Invocation(
        $this,
        self::$__routines['.$i.'],
        ['.$decl->arguments().']
      ));
    ');
  }

  /**
   * Returns the proxy type
   *
   * @return lang.XPClass
   */
  public function type() {
    return $this->type;
  }
}