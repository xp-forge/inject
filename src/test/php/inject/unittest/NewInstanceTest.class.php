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
      'inject.unittest.fixture.FileSystem',
      [],
      $definition
    );
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