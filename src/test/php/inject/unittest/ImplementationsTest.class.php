<?php namespace inject\unittest;

use inject\unittest\fixture\{URI, Endpoint, Service};
use inject\{Injector, ProvisionException};
use test\{Assert, Before, Expect, Test, Values};

class ImplementationsTest {
  private $uris;

  #[Before]
  private function uris() {
    $this->uris= [
      'dev'  => new URI('http://localhost'),
      'prod' => new URI('https://example.com'), 
    ];
  }

  /** @return inject.Injector */
  private function fixture() {
    $fixture= new Injector();
    foreach ($this->uris as $name => $uri) {
      $fixture->bind(URI::class, $uri, $name);
    }
    return $fixture;
  }

  #[Test, Values(['dev', 'prod'])]
  public function implementations_named($name) {
    Assert::equals($this->uris[$name], $this->fixture()->implementations(URI::class)->named($name));
  }

  #[Test]
  public function default_implementation() {
    Assert::equals($this->uris['dev'], $this->fixture()->implementations(URI::class)->default());
  }

  #[Test, Expect(ProvisionException::class)]
  public function no_implementations() {
    $this->fixture()->implementations(Endpoint::class);
  }

  #[Test, Expect(ProvisionException::class)]
  public function unknown_implementation() {
    $this->fixture()->implementations(URI::class)->named('stage');
  }

  #[Test]
  public function inject() {
    $fixture= $this->fixture();
    Assert::equals($this->uris, $fixture->get(Service::class)->uris);
  }
}