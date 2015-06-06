Dependency injection for the XP Framework change log
====================================================

## ?.?.? / ????-??-??

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
  be called `LegacyInjector` or somethong. I'll leave this to another place
  (@thekid)

## 0.4.0 / 2015-01-10

* Made available via Composer - @thekid

## 0.3.0 / 2014-09-23

* First public release - @thekid
