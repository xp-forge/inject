<?php namespace inject;

interface Lookup {

  /** @return var */
  public function get();

  /** @return ?self */
  public function provided();
}