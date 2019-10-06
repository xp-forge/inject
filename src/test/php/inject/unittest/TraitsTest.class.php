<?php namespace inject\unittest;

use inject\Injector;
use inject\Traits;
use inject\unittest\fixture\Api;
use inject\unittest\fixture\DSN;
use lang\ClassLoader;
use unittest\TestCase;

class TraitsTest extends TestCase {

  /**
   * Creates a storage subtype from a given definition
   *
   * @param  string... $traits
   * @return object
   */
  protected function fixture(...$traits) {
    return ClassLoader::defineType(
      'inject.unittest.fixture.'.$this->name,
      ['kind' => 'class', 'extends' => null, 'implements' => [], 'use' => $traits],
      ['injected' => function() { return get_object_vars($this); }]
    );
  }

  #[@test]
  public function single_trait() {
    $dsn= 'mysql://test@example.com';
    $inject= (new Injector())->bind('string', $dsn, 'dsn');

    $this->assertEquals(
      ['dsn' => $dsn],
      $inject->get($this->fixture(Traits::class, DSN::class))->injected()
    );
  }

  #[@test]
  public function multiple_traits() {
    $dsn= 'mysql://test@example.com';
    $endpoint= 'https://api.example.com';
    $inject= (new Injector())->bind('string', $dsn, 'dsn')->bind('string', $endpoint, 'endpoint');

    $this->assertEquals(
      ['dsn' => $dsn, 'endpoint' => $endpoint],
      $inject->get($this->fixture(Traits::class, DSN::class, Api::class))->injected()
    );
  }
}