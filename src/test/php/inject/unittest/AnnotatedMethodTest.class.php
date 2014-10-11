<?php namespace inject\unittest;

use util\Currency;
use lang\Runnable;
use lang\IllegalArgumentException;
use unittest\TestCase;

class AnnotatedMethodTest extends AnnotationsTest {

  #[@test]
  public function not_annotated_method_not_called() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'something' => function($param) { throw new \lang\IllegalStateException('Should not be called'); }
    ]));
    $this->inject->get('inject.unittest.fixture.Storage');
  }

  #[@test]
  public function with_inject_annotation_and_type() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject(type= "unittest.TestCase")] inject' => function($param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $this->inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function with_inject_annotation_and_restriction() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject] inject' => function(TestCase $param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $this->inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function with_inject_parameter_annotations() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@$test: inject, @$cur: inject(name= "EUR")] inject' => function(TestCase $test, Currency $cur) {
        $this->injected= [$test, $cur];
      }
    ]));
    $this->assertEquals([$this, Currency::$EUR], $this->inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Unknown injection type/')]
  public function injecting_unbound_into_method() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject] inject' => function(Runnable $param) { /* Empty */ }
    ]));
    $this->inject->get('inject.unittest.fixture.Storage');
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error invoking/')]
  public function throwing_an_exception_raises_ProvisionException() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject] inject' => function(TestCase $param) { throw new IllegalArgumentException('Test'); }
    ]));
    $this->inject->get('inject.unittest.fixture.Storage');
  }
}