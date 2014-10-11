<?php namespace inject\unittest;

use inject\Injector;
use unittest\TestCase;
use lang\XPClass;
use inject\unittest\fixture\FileSystem;

class ProvidersTest extends TestCase {

  #[@test]
  public function type_provider() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', XPClass::forName('inject.unittest.fixture.FileSystem'));
    $this->assertInstanceOf(
      'inject.TypeProvider',
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')
    );
  }

  #[@test]
  public function type_provider_get() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', XPClass::forName('inject.unittest.fixture.FileSystem'));
    $this->assertInstanceOf(
      'inject.unittest.fixture.FileSystem',
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')->get()
    );
  }

  #[@test]
  public function instance_provider() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', new FileSystem());
    $this->assertInstanceOf(
      'inject.InstanceProvider',
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')
    );
  }

  #[@test]
  public function instance_provider_get() {
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', new FileSystem());
    $this->assertInstanceOf(
      'inject.unittest.fixture.FileSystem',
      $inject->get('inject.Provider<inject.unittest.fixture.Storage>')->get()
    );
  }
}