<?php namespace inject\unittest;

use inject\Injector;
use inject\unittest\fixture\FileSystem;
use inject\unittest\fixture\Storage;
use lang\IllegalStateException;
use lang\ClassLoader;

class MemberInjectionTest extends \unittest\TestCase {
  private $inject;

  /**
   * Creates injector and binds FileSystem
   *
   * @return void
   */
  public function setUp() {
    $this->inject= new Injector();
    $this->inject->bind(Storage::class, new FileSystem());
  }

  #[@test]
  public function injecting_fields() {
    $fixture= $this->inject->into(newinstance('lang.Object', [], [
      '#[@inject(type= "inject.unittest.fixture.Storage")] storage' => null
    ]));

    $this->assertInstanceOf(Storage::class, $fixture->storage);
  }

  #[@test]
  public function injecting_methods() {
    $fixture= $this->inject->into(newinstance('lang.Object', [], [
      'storage' => null,
      '#[@inject] useStorage' => function(Storage $storage) { $this->storage= $storage; }
    ]));

    $this->assertInstanceOf(Storage::class, $fixture->storage);
  }

  #[@test]
  public function injecting_methods_via_param_annotation() {
    $fixture= $this->inject->into(newinstance('lang.Object', [], [
      'storage' => null,
      '#[@$storage: inject] useStorage' => function(Storage $storage) { $this->storage= $storage; }
    ]));

    $this->assertInstanceOf(Storage::class, $fixture->storage);
  }

  #[@test]
  public function injecting_method_with_type() {
    $fixture= $this->inject->into(newinstance('lang.Object', [], [
      'storage' => null,
      '#[@inject(type= "inject.unittest.fixture.Storage")] useStorage' => function($storage) { $this->storage= $storage; }
    ]));

    $this->assertInstanceOf(Storage::class, $fixture->storage);
  }

  #[@test]
  public function get_does_not_inject_members() {
    $class= ClassLoader::defineClass('NotInjected', 'lang.Object', [Storage::class], [
      '#[@inject(type= "inject.unittest.fixture.Storage")] storage' => null,
      '#[@inject(type= "inject.unittest.fixture.Storage")] useStorage' => function($storage) {
        throw new IllegalStateException('Should not be reached');
      },
    ]);

    $fixture= $this->inject->get($class);
    $this->assertNull($fixture->storage);
  }
}