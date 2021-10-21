Dependency injection for the XP Framework change log
====================================================

## ?.?.? / ????-??-??

* Made compatible with PHP 8.1 - add `ReturnTypeWillChange` attributes to
  array access, see https://wiki.php.net/rfc/internal_method_return_types

## 5.1.0 / 2021-07-17

* Changed implementation to check named bindings before resorting to
  parameters' default values
  (@thekid)

## 5.0.2 / 2021-07-17

* Fixed compatibility with XP 10.6.0's nullable types - @thekid

## 5.0.1 / 2020-11-29

* Fixed `ParseError (syntax error, unexpected token "@")` in PHP 8.0
  (@thekid)

## 5.0.0 / 2020-04-10

* Implemented xp-framework/rfc#334: Drop PHP 5.6:
  . **Heads up:** Minimum required PHP version now is PHP 7.0.0
  . Rewrote code base, grouping use statements
  . Converted `newinstance` to anonymous classes
  . Rewrote `isset(X) ? X : default` to `X ?? default`
  (@thekid)

## 4.4.0 / 2020-04-05

* Merged PR #23: Binding DSL - @thekid

## 4.3.2 / 2020-04-05

* Implemented RFC #335: Remove deprecated key/value pair annotation syntax
  (@thekid)

## 4.3.1 / 2019-12-01

* Made compatible with XP 10 - @thekid

## 4.3.0 / 2019-10-06

* Merged PR #21: Add `[@inject("name")]` as short form of *(name= "name")*
  (@thekid)
* Added PHP 7.3 and PHP 7.4 to test matrix - @thekid

## 4.2.0 / 2018-05-22

* Implemented feature request #18: Injection names. Names are now calculated
  from parameter names if omitted
  (@thekid)

## 4.1.0 / 2017-10-28

* Added PHP 7.2 to test matrix - @thekid
* Made `@inject` annotation optional, now only needs to be supplied for
  named bindings.
  (@thekid)

## 4.0.0 / 2017-06-16

* Added forward compatibility with XP 9.0.0 - @thekid

## 3.1.0 / 2016-08-28

* Added forward compatibility with XP 8.0.0: Refrain from using deprecated
  `util.Properties::fromString()`
  (@thekid)

## 3.0.0 / 2016-08-15

* **Heads up: Dropped PHP 5.5 support**. Minimum PHP version is now PHP 5.6.0
  (@thekid)

## 2.2.0 / 2016-06-07

* Merged PR #13: Array binding - @thekid

## 2.1.0 / 2016-05-06

* Merged PR #12: Add support for type unions - @thekid
* Fixed `lang.NullPointerException (Undefined variable: type)` when 
  performing constructor injection where a required parameter's value
  is not existant (e.g., because it hasn't been bound).
  (@thekid)

## 2.0.0 / 2016-05-01

* Merged PR #11: Remove support for field and method injection - @thekid
* Merged PR #10: Add support for inheritance via property file section.
  **Heads up: Changes namespace import syntax in property files!** - read
  the pull request for details on why and how to migrate.
  (@thekid)

## 1.1.0 / 2016-05-01

* Merged PR #9: Support instances without lang.Object as base, adding
  support for baseless classes and `Value` instances (see xp-framework/rfc#297)
  (@thekid)

## 1.0.2 / 2016-04-15

* Fixed primitive bindings containing commas in ConfiguredBindings class.
  (@thekid)

## 1.0.1 / 2016-04-15

* Fixed string arguments containing commas in ConfiguredBindings class:
  `peer.URL[api]=peer.URL("https://user:pass,word@example.com")`
  (@thekid)

## 1.0.0 / 2016-02-21

* Added version compatibility with XP 7 - @thekid

## 0.7.0 / 2015-09-27

* **Heads up: Bumped minimum PHP version required to PHP 5.5**. See PR #8
  (@thekid)

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
