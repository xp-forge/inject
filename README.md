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

* **Binding a class**: The typical usecase, where we bind an interface to its concrete implementation.
* **Binding an instance**: By binding a type to an existing instance, we can create a *singleton* model.
* **Binding a provider**: If we need more complicated code to create an instance, we can bind to a provider.

```php
use inject\Injector;
use inject\Bindings;

// Manually
$injector= new Injector();
$injector->bind('com.example.Report', 'com.example.HtmlReport');
$injector->bind('com.example.Storage', new InFileSystem('.'));
$injector->bind('string', 'Report title', 'title');

// Reusable via Bindings instances
class ApplicationDefaults extends Bindings {

  public function configure($injector) {
    $injector->bind('com.example.Report', 'com.example.HtmlReport');
    $injector->bind('com.example.Storage', new InFileSystem('.'));
    $injector->bind('string', 'Report title', 'title');
  }
}

$injector= new Injector(new ApplicationDefaults());
```

Instance creation
-----------------
Keep in mind: ***"injector.get() is the new 'new'"***. To create objects and perform injection, use the Injector's get() method instead of using the `new` keyword or factories.

```php
use inject\Injector;

$injector->bind('com.example.Report', 'com.example.HtmlReport');

// Explicit binding: Lookup finds binding to HtmlReport, creates instance.
$instance= $injector->get('com.example.Report');

// Implicit binding: No previous binding, TextReport instantiable, thus created.
$instance= $injector->get('com.example.TextReport');
```

Manual calls are usually not necessary though, instead you'll use injection:

Injection
---------
Injection is performed by looking at a type's constructor. If it's annotated with an `@inject` annotation, bound values will be passed according to the given type hint.

```php
// Single parameter
class ReportImpl extends \lang\Object implements Report {

  #[@inject]
  public function __construct(ReportWriter $writer) { ... }
}

// Multiple parameters
class ReportImpl extends \lang\Object implements Report {

  #[@inject]
  public function __construct(ReportWriter $writer, Format $format) { ... }
}
```

You can supply name and type by using parameter annotations:

```php
class ReportImpl extends \lang\Object implements Report {

  #[@inject, @$title: inject(type= 'string', name= 'title')]
  public function __construct(ReportWriter $writer, Format $format, $title) { ... }
}
```

When a required parameter is encountered and there is no bound value for this parameter, an `inject.ProvisionException` is raised.

```php
class ReportWriter extends \lang\Object implements Writer {

  #[@inject]
  public function __construct(Storage $storage) { ... }
}

$injector= new Injector();
$report= $injector->get('com.example.ReportWriter');  // *** Storage not bound
```

Method and field injection are not supported.

Providers
---------
Providers allow implementing lazy-loading semantics. Every type bound to the injector can also be retrieved by a provider. Invoking its get() method will instantiate it.

```php
$provider= $injector->get('inject.Provider<com.example.writers.ReportWriter>');

// ...later on
$instance= $provider->get();
```

