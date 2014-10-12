<?php namespace inject\aop;

use lang\reflect\Routine;

/**
 * Method declaration
 *
 * @test  xp://inject.unittest.aop.MethodDeclarationTest
 */
class MethodDeclaration extends \lang\Object {
  protected $name;
  protected $signature;
  protected $arguments;

  /**
   * Creates a new instance from a given routine
   *
   * @param  lang.reflect.Routine $routine
   */
  public function __construct(Routine $routine) {
    $arguments= $signature= '';
    foreach ($routine->getParameters() as $param) {
      $name= $param->getName();
      $arguments.= ', $'.$name;

      if ($restrict= $param->getTypeRestriction()) {
        $signature.= ', '.strtr($restrict, '.', '\\').' $'.$name;
      } else {
        $signature.= ', $'.$name;
      }

      if ($param->isOptional()) {
        $signature.= '= '.var_export($param->getDefaultValue(), true);
      }
    }

    $this->name= $routine->getName();
    $this->signature= $signature ? substr($signature, 2) : '';
    $this->arguments= $arguments ? substr($arguments, 2) : '';
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
    return 'function '.$this->name.'('.$this->signature.') { '.$body.' }';
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