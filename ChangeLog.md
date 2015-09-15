Dependency injection for the XP Framework change log
====================================================

## ?.?.? / ????-??-??

## 0.6.0 / 2015-09-15

* Added PHP 7.0 support - @thekid

## 0.5.0 / 2015-06-07

* Added `inject.ConfiguredBindings` which reads bindings from a .ini
  file. See pull request #6
  (@thekid)
* Added class `inject.Named`. Extending from this class will allow to
  create bindings on demand. See pull request #5
  (@thekid)
* Improved error messages when injecting fields and parameters
  (@thekid)
* Changed `@inject` annotation behavior:
  . If a method annotation is present, injection is performed for all
    parameters using their types. Previously, this annotation was only
    taken into consideration for the first parameter.
  . Parameter annotations are only necessary when supplying types or
    names for a single parameter.
  . Injection is also performed for optional bound parameters instead
    of silently ignoring them.
  (@thekid)
* Removed field and method injection via `get()`, now only supports
  constructor injection. If you need injection for fields, you may
  use `$inject->into($instance)` after getting an instance.
  (@thekid)
* Removed `inject.XPInjector`. Its name is misleading, its implementation
  dependant on whether certain other modules are loaded or not. It should
  be called `LegacyInjector` or something. If necessary, use `inject.Named`. 
  (@thekid)

## 0.4.0 / 2015-01-10

* Made available via Composer - @thekid

## 0.3.0 / 2014-09-23

* First public release - @thekid
