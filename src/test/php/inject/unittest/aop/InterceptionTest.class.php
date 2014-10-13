<?php namespace inject\unittest\aop;

use inject\Injector;
use inject\aop\ProxyProvider;
use inject\aop\Methods;
use inject\aop\MethodInvocation;
use unittest\TestCase;

class InterceptionTest extends TestCase {

  #[@test]
  public function all_methods() {
    $log= create('new util.collections.Vector<inject.aop.MethodInvocation>');
    $inject= new Injector();
    $inject->bind('inject.unittest.fixture.Storage', new ProxyProvider(
      'inject.unittest.fixture.FileSystem',
      $inject,
      Methods::$ALL,
      newinstance('inject.aop.MethodInterception', [], [
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