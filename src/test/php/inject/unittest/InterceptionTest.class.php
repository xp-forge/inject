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
          return $invocation->proceed();
        }
      ])
    ));

    $instance= $inject->get('inject.unittest.fixture.Storage');
    $this->assertEquals('Stored "Hello"', $instance->store('Hello'));
    $this->assertEquals(
      [new MethodInvocation($instance, $instance->getClass()->getParentclass()->getMethod('store'), ['Hello'])],
      $log->elements()
    );
  }
}