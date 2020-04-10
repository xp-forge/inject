Inject
======

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/inject.svg)](http://travis-ci.org/xp-forge/inject)
[![XP Framework Mdodule](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/inject/version.png)](https://packagist.org/packages/xp-forge/inject)

The inject package contains the XP framework's dependency injection API. Its entry point class is the "Injector".

Binding
-------
Values can be bound to the injector by using its `bind()` method. It accepts the type to bind to, an optional name and these different scenarios:

* **Binding a class**: The typical usecase, where we bind an interface to its concrete implementation.
* **Binding an instance**: By binding a type to an existing instance, we can create a *singleton* model.
* **Binding a provider**: If we need more complicated code to create an instance, we can bind to a provider.
* **Binding a named lookup**: If we want control over the binding lookups for a type, we can bind to a `Named` instance.

```php
use inject\{Injector, Bindings};
use com\example\{Report, HtmlReport, Storage, InFileSystem};

// Manually
$injector= new Injector();
$injector->bind(Report::class, HtmlReport::class);
$injector->bind(Storage::class, new InFileSystem('.'));
$injector->bind('string', 'Report title', 'title');

// Reusable via Bindings instances
class ApplicationDefaults extends Bindings {

  public function configure($injector) {
    $injector->bind(Report::class, HtmlReport::class);
    $injector->bind(Storage::class, new InFileSystem('.'));
    $injector->bind('string', 'Report title', 'title');
  }
}

$injector= new Injector(new ApplicationDefaults());
```

Instance creation
-----------------
Keep in mind: **"injector.get() is the new 'new'"**. To create objects and perform injection, use the Injector's get() method instead of using the `new` keyword or factories.

```php
use inject\Injector;

$injector->bind(Report::class, HtmlReport::class);

// Explicit binding: Lookup finds binding to HtmlReport, creates instance.
$instance= $injector->get(Report::class);

// Implicit binding: No previous binding, TextReport instantiable, thus created.
$instance= $injector->get(TextReport::class);
```

Manual calls are usually not necessary though, instead you'll use injection:

Injection
---------
Injection is performed by looking at a type's constructor. Bound values will be passed according to the given type hint.

```php
class ReportImpl implements Report {

  public function __construct(ReportWriter $writer) { ... }
}
```

You can supply name and type by using parameter annotations:

```php
class ReportImpl implements Report {

  #[@$title: inject(type= 'string', name= 'title')]
  public function __construct(ReportWriter $writer, Format $format, $title) { ... }
}
```

When a required parameter is encountered and there is no bound value for this parameter, an `inject.ProvisionException` is raised.

```php
class ReportWriter implements Writer {

  public function __construct(Storage $storage) { ... }
}

$injector= new Injector();
$report= $injector->get(ReportWriter::class);  // *** Storage not bound
```

Method and field injection are not supported.

Configuration
-------------
As seen above, bindings can be used instead of manually performing the wiring. You might want to configure some of your app's settings externally instead of harcoding them. Use the `inject.ConfiguredBindings` class for this:

```php
$injector= new Injector(
  new ApplicationDefaults(),
  new ConfiguredBindings(new Properties('etc/app.ini'))
);
```

The syntax for these INI files is simple:

```ini
scriptlet.Session=com.example.session.FileSystem("/tmp")
string[name]="Application"
```

Providers
---------
Providers allow implementing lazy-loading semantics. Every type bound to the injector can also be retrieved by a provider. Invoking its get() method will instantiate it.

```php
$provider= $injector->get('inject.Provider<com.example.writers.ReportWriter>');

// ...later on
$instance= $provider->get();
```

Named lookups
-------------
If we need control over the lookup, we can bind instances of `Named`:

```php
use inject\{Injector, Named, InstanceBinding};
use com\example\Value;

$inject= new Injector();
$inject->bind(Value::class, newinstance(Named::class, [], [
  'provides' => function($name) { return true; },
  'binding'  => function($name) { return new InstanceBinding(new Value($name)); }
]));

$value= $inject->get(Value::class, 'default');  // new Value("default")
```

