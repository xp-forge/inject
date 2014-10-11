<?php namespace inject\unittest;

use inject\Injector;
use inject\ProxyProvider;
use inject\MethodInvocation;
use unittest\TestCase;

class InterceptionTest extends TestCase {

  #[@test]
  public function _() {
    $log= create('new util.collections.Vector<inject.MethodInvocation>');
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', new ProxyProvider(
      'inject.unittest.fixture.FileSystem',
      $inject,
      newinstance('inject.MethodInterception', [], [
        'invoke' => function($invocation) use($log) {
          $log[]= $invocation;
        }
      ])
    ));

    $instance= $inject->get('inject.unittest.fixture.Storage');
    $instance->store('Hello');
    $this->assertEquals([new MethodInvocation($instance, 'store', ['Hello'])], $log->elements());
  }
}