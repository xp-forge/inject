<?php namespace inject\unittest;

use util\Currency;
use lang\Runnable;
use unittest\TestCase;

class AnnotatedFieldTest extends AnnotationsTest {

  #[@test]
  public function with_inject_annotation_and_type() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject(type= "unittest.TestCase")] injected' => null,
    ]));
    $this->assertEquals($this, $this->inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test]
  public function with_inject_and_type_annotations() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject, @type("unittest.TestCase")] injected' => null,
    ]));
    $this->assertEquals($this, $this->inject->get('inject.unittest.fixture.Storage')->injected);
  }

  #[@test, @expect(class= 'inject.ProvisionException', withMessage= '/Error setting/')]
  public function injecting_unbound_into_field() {
    $this->inject->bind('inject.unittest.fixture.Storage', $this->newStorage([
      '#[@inject, @type("lang.Runnable")] injected' => null,
    ]));
    $this->inject->get('inject.unittest.fixture.Storage');
  }
}