<?php namespace inject\unittest\fixture;

trait Api {
  private $endpoint;

  #[@inject(name= 'endpoint', type= 'string')]
  public function endpoint($endpoint) {
    $this->endpoint= $endpoint;
  }
}