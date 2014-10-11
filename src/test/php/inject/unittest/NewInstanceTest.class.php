<?php namespace inject\unittest;

use inject\Injector;
use unittest\TestCase;
use util\Currency;
use lang\ClassLoader;
use lang\Runnable;

class NewInstanceTest extends TestCase {

  /**
   * Creates a storage subtype from a given definition
   *
   * @param  [:var] $definition
   * @return inject.unittest.fixture.Storage
   */
  protected function newStorage($definition) {
    return ClassLoader::defineClass(
      'inject.unittest.fixture.'.$this->name,
      'lang.Object',
      ['inject.unittest.fixture.Storage'],
      $definition
    );
  }

  #[@test]
  public function constructor_with_inject_annotation_and_type() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject(type= "unittest.TestCase")] __construct' => function($param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function constructor_with_inject_annotation_and_restriction() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject] __construct' => function(TestCase $param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function constructor_with_inject_parameter_annotations() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this);
    $inject->bind('util.Currency', Currency::$EUR, 'EUR');
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@$test: inject, @$cur: inject(name= "EUR")] __construct' => function(TestCase $test, Currency $cur) {
        $this->injected= [$test, $cur];
      }
    ]));
    $this->assertEquals([$this, Currency::$EUR], $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error creating an instance/')]
  public function constructor_injecting_unbound() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject] __construct' => function(Runnable $param) { /* Empty */ }
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error creating an instance/')]
  public function constructor_throwing_an_exception_raises_ProvisionException() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '__construct' => function() { throw new \lang\IllegalArgumentException('Test'); }
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test]
  public function field_with_inject_annotation_and_type() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject(type= "unittest.TestCase")] injected' => null,
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function field_with_inject_and_type_annotations() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject, @type("unittest.TestCase")] injected' => null,
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error setting/')]
  public function field_injecting_unbound() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject, @type("lang.Runnable")] injected' => null,
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test]
  public function not_annotated_method_not_called() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'something' => function($param) { throw new \lang\IllegalStateException('Should not be called'); }
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test]
  public function method_with_inject_annotation_and_type() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject(type= "unittest.TestCase")] inject' => function($param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function method_with_inject_annotation_and_restriction() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@inject] inject' => function(TestCase $param) { $this->injected= $param; }
    ]));
    $this->assertEquals($this, $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function method_with_inject_parameter_annotations() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this);
    $inject->bind('util.Currency', Currency::$EUR, 'EUR');
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      'injected' => null,
      '#[@$test: inject, @$cur: inject(name= "EUR")] inject' => function(TestCase $test, Currency $cur) {
        $this->injected= [$test, $cur];
      }
    ]));
    $this->assertEquals([$this, Currency::$EUR], $inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Unknown injection type/')]
  public function method_injecting_unbound() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject] inject' => function(Runnable $param) { /* Empty */ }
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error invoking/')]
  public function method_throwing_an_exception_raises_ProvisionException() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this); 
    $inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject] inject' => function(TestCase $param) { throw new \lang\IllegalArgumentException('Test'); }
    ]));
    $inject->get('inject.unittest.fixture.Storage');
  }

  #[@test]
  public function newInstance_performs_injection() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this);
    $storage= $this->newStorage([
      'injected' => null,
      '#[@inject] __construct' => function(TestCase $param) { $this->injected= $param; }
    ]);
    $this->assertEquals($this, $inject->newInstance($storage)->injected);
  }

  #[@test]
  public function newInstance_also_accepts_arguments() {
    $inject= new Injector();
    $storage= $this->newStorage([
      'injected' => null,
      '__construct' => function(TestCase $param) { $this->injected= $param; }
    ]);
    $this->assertEquals($this, $inject->newInstance($storage, [$this])->injected);
  }

  #[@test]
  public function newInstance_performs_partial_injection_with_required_parameter() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this);
    $storage= $this->newStorage([
      'injected' => null,
      '#[@inject] __construct' => function(TestCase $param, $verify) { $this->injected= [$param, $verify]; }
    ]);
    $this->assertEquals([$this, true], $inject->newInstance($storage, [null, true])->injected);
  }

  #[@test]
  public function newInstance_performs_partial_injection_with_optional_parameter() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this);
    $storage= $this->newStorage([
      'injected' => null,
      '#[@inject] __construct' => function(TestCase $param, $verify= true) { $this->injected= [$param, $verify]; }
    ]);
    $this->assertEquals([$this, true], $inject->newInstance($storage)->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error creating an instance of .+/')]
  public function newInstance_catches_iae_when_creating_class_instances() {
    $inject= new Injector();
    $storage= $this->newStorage('{
      #[@inject]
      private function __construct() { }
    }');
    $inject->newInstance($storage);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error setting .+::\$fixture/')]
  public function newInstance_catches_iae_when_setting_private_fields() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this);
    $storage= $this->newStorage('{
      #[@inject(type= "unittest.TestCase")]
      private $fixture;
    }');
    $inject->newInstance($storage);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error invoking .+::fixture/')]
  public function newInstance_catches_iae_when_invoking_private_methods() {
    $inject= new Injector();
    $inject->bind('unittest.TestCase', $this);
    $storage= $this->newStorage('{
      #[@inject]
      private function fixture(\unittest\TestCase $param) { }
    }');
    $inject->newInstance($storage);
  }
}