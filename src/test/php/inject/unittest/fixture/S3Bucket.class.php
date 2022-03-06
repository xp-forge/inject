<?php namespace inject\unittest\fixture;

class S3Bucket implements Storage {
  private $bucket;

  /** @param string $bucket */
  public function __construct($bucket) {
    $this->bucket= $bucket;
  }
}