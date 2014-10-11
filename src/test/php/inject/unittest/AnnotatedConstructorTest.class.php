<?php namespace inject\unittest;

use util\Currency;
use lang\Runnable;
use lang\IllegalArgumentException;
use unittest\TestCase;

class AnnotatedConstructorTest extends AnnotationsTest {

  #[@test]
  public function with_inject_annotation_and_type() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject(type= "unittest.TestCase")] __construct' => function($param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $this->inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function with_inject_annotation_and_restriction() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject] __construct' => function(TestCase $param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $this->inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function with_inject_parameter_annotations() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@$test: inject, @$cur: inject(name= "EUR")] __construct' => function(TestCase $test, Currency $cur) {
        $this->injected= [$test, $cur];
      }
    ]));
    $this->assertEquals([$this, Currency::$EUR], $this->inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error creating an instance/')]
  public function injecting_unbound_into_constructor() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject] __construct' => function(Runnable $param) { /* Empty */ }
    ]));
    $this->inject->get('inject.unittest.fixture.Storage');
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error creating an instance/')]
  public function throwing_an_exception_from_constructor_raises_ProvisionException() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '__construct' => function() { throw new IllegalArgumentException('Test'); }
    ]));
    $this->inject->get('inject.unittest.fixture.Storage');
  }
}