<?php namespace inject;

use lang\reflect\Routine;

class MethodDeclaration extends \lang\Object {

  /**
   * Creates a new instance from a given routine
   *
   * @param  lang.reflect.Routine $routine
   */
  public function __construct(Routine $routine) {
    $arguments= $signature= '';
    foreach ($routine->getParameters() as $i => $param) {
      $arguments.= ', $__a'.$i;

      if ($restrict= $param->getTypeRestriction()) {
        $signature.= ', '.$restrict.' $__a'.$i;
      } else {
        $signature.= ', $__a'.$i;
      }

      if ($param->isOptional()) {
        $signature.= '= '.var_export($param->getDefaultValue(), true);
      }
    }

    $this->name= $routine->getName();
    $this->signature= substr($signature, 2);
    $this->arguments= substr($arguments, 2);
  }

  /** @return string */
  public function name() { return $this->name; }

  /** @return string */
  public function signature() { return $this->signature; }

  /** @return string */
  public function arguments() { return $this->arguments; }

  /**
   * Creates a declaration with a method body
   *
   * @param  string $body
   * @return string
   */
  public function withBody($body) { 
    return 'function '.$this->name.'('.$this->signature.') {'.$body.'}';
  }

  /**
   * Creates a string representatio
   *
   * @return string
   */
  public function toString() {
    return $this->getClassName().'<function '.$this->name.'('.$this->signature.')>';
  }
}