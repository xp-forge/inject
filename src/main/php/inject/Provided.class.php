<?php namespace inject;

interface Provided {

  /** @return var */
  public function get();

  /** @return ?self */
  public function provided();
}