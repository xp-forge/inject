Inject
======

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/inject.svg)](http://travis-ci.org/xp-forge/inject)
[![XP Framework Mdodule](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)
[![Required HHVM 3.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_4plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/inject/version.png)](https://packagist.org/packages/xp-forge/inject)

The inject package contains the XP framework's dependency injection API. Its entry point class is the "Injector".

Binding
-------
Values can be bound to the injector by using its `bind()` method. It accepts the type to bind to, an optional name and three different scenarios:

* **Binding an class**: The typical usecase, where we bind an interface to its concrete implementation.
* **Binding an instance**: By binding a type to an existing instance, we can create a "singleton" model.
* **Binding a provider**: If we need more complicated code to create an instance, we can bind to a provider.

```php
// Manually
$injector= new Injector();
$injector->bind('com.example.Report', 'com.example.HtmlReport');

// Reusable via Bindings instances
class ApplicationDefaults extends Bindings {
  public function configure($injector) {
    $injector->bind('com.example.Report', 'com.example.HtmlReport');
  }
}
$injector= new Injector(new ApplicationDefaults());
```

Instance creation
-----------------
Keep in mind: ***"injector.get() is the new 'new'"***. To create objects and perform injection, use the Injector's get() method instead of using the `new` keyword or factories.

```php
$injector->bind('com.example.Report', 'com.example.HtmlReport');

// Explicit binding: Lookup finds binding to HtmlReport, creates instance.
$instance= $injector->get('com.example.Report');

// Implicit binding: No previous binding, TextReport instantiable, thus created.
$instance= $injector->get('com.example.TextReport');
```

Manual calls are usually not necessary though, instead you'll use the injection:

Injection
---------
Injection is performed by looking at a type's constructor, its fields and methods and checking for the `@inject` annotation.

```php
// Constructor injection
class ReportImpl extends Object implements Report {
  #[@inject]
  public function __construct(ReportWriter $writer) { ... }
}

// Method injection
class ReportImpl extends Object implements Report {
  #[@inject]
  public function setWriter(ReportWriter $writer) { ... }
}

// Field injection
class ReportImpl extends Object implements Report {
  #[@inject, @type = 'com.example.writers.ReportWriter']
  public $writer;
}
```

Providers
---------
Providers allow implementing lazy-loading semantics. Every type bound to the injector can also be retrieved by a provider. Invoking its get() method will instantiate it.

```php
$provider= $injector->get('inject.Provider<com.example.writers.ReportWriter>');

// ...later on
$instance= $provider->get();
```

