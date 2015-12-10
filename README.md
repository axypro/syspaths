# axy\syspaths

Specifying paths within the system.

[![Latest Stable Version](https://img.shields.io/packagist/v/axy/syspaths.svg?style=flat-square)](https://packagist.org/packages/axy/syspaths)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.4-8892BF.svg?style=flat-square)](https://php.net/)
[![Build Status](https://img.shields.io/travis/axypro/syspaths/master.svg?style=flat-square)](https://travis-ci.org/axypro/syspaths)
[![Coverage Status](https://coveralls.io/repos/axypro/syspaths/badge.svg?branch=master&service=github)](https://coveralls.io/github/axypro/syspaths?branch=master)
[![License](https://poser.pugx.org/axy/syspaths/license)](LICENSE)

* The library does not require any dependencies (except composer packages).
* Tested on PHP 5.4+, PHP 7, HHVM (on Linux), PHP 5.5 (on Windows).
* Install: `composer require axy/syspaths`.
* License: [MIT](LICENSE).

### Documentation

The library allows to store of the directories structure within a certain system.
Inside an application for example.

```php
/* App init. __DIR__ is /www/example.loc */
use axy\syspaths\Paths;

$paths = new Paths(__DIR__, [
    'htdocs' => 'www',
    'source' => 'src',
    'templates' => 'templates',
]);

/* Further */

$tplFN = $app->paths->templates.'/default.twig'; // /www/example.loc/templates/default.twig
```

Define paths in one place makes it easy to change the structure.

#### API

The library provides a single class `axy\syspaths\Paths`.

The constructor:

```
Paths::__construct(string $root [, array $patterns]);
```

The only required arguments is `$root` - the root directory of the system.

##### $patterns

`$patterns` specifies a list of sub-paths.
It can be specified via the constructor (as in the example above).
Or by overriding:

```php
/**
 * @property-read string $www
 * @property-read string $templates
 */
class MyPaths extends Paths
{
    protected $patterns = [
        'htdocs' => 'www',
        'templates' => 'templates',
    ];
}
```

Now available autocomplete in IDE.

Or you can define the property `$patterns` and also specify the argument of the constructor.
In this case these two list recursively merged.
For example, define a system class and redefine some paths for tests.

##### Access to paths

Via magic methods.

```php
$paths->templates; // /www/example.loc/templates
$paths->root; // /www/example.loc

isset($paths->htdocs); // TRUE

$paths->htdocs = 'new-www'; // Exception: Paths is read-only
```

#### Patterns

```php
[
    'www' => 'www',
    'images' => ':www:/i',
    'icons' => ':images:/icons',
    'logo' => ':icons:/logo.gif',
    'tmp' => '/tmp',
]
```

If a path pattern begins with a colon then it is a link to the other path.
Such links are processed recursively (circular references are not tracked):

```php
$paths->icons; // /www/example.loc/www/i/icons/logo.gif
```

If a path pattern begins with `/` then it is an absolute path:

```php
$paths->tmp; // /tmp
```

Other patterns is relative to the root.
`www` is equivalent to `:root:/www`.

#### Nested paths

```php
[
    'htdocs' => 'www',
    'templates' => [
        'root' => 'templates',
        'layouts' => 'layouts',
        'admin' => ':layouts:/admin',
        'profile' => ':admin:/profile.twig',
    ],
]
```

An array defines a nested object Paths.

```php
$paths->templates->profile; // /www/example.loc/templates/layouts/admin/profile.twig
```

For Paths-objects defined `__toString()`:

```php
$paths->templates.'/mails'; // /www/example.loc/templates/mails
```

The nested array must contains the field `root`.
It may be link: `'root' => ':htdocs:/templates'`.

The nested array may contains the field `__classname` for the class of the nested path.
By defaults it is `axy\syspaths\Paths`.

```php
/**
 * @property-read string $htdocs
 * @property-read TemplatesPaths $templates
 */
class MyPaths extends Paths
{
    $patterns = [
        'htdocs' => 'www',
        'templates' => [
            'root' => 'templates',
            '__classname' => 'TemplatesPaths',
        ],
    ]
}

/**
 * @property-read string $layout
 * @property-read string $admin
 * @property-read string $profile
 */
class TemplatesPaths extends Paths
{
    $patterns = [
        'layouts' => 'layouts',
        'admin' => ':layouts:/admin',
        'profile' => ':admin:/profile.twig',    
    ];
}
```

Now we have the full auto-complete: `$app->paths->templates->profile`.

Links can consist of several components:

```php
[
    'templates' => [
        'root' => 'templates',
        'layouts' => 'layouts',
        'admin' => ':layouts:/admin',
    ],
    'profileTemplate' => ':templates.admin:/profile.twig',
]
```

#### Creating paths

```php
$paths->templates->layouts.'/tpl.twig';
```

Or via the method `create` (or `__invoke`):

```php
$paths->create(':templates.layouts:/tpl.twig');
$paths(':templates.layouts:/tpl.twig');
```

```
Paths::create($pattern, $real = false);
```

If specified the second argument `$real` executed `realpath()` for the result.
If the path is not exists then returned `NULL`.

#### Exceptions

In the namespace `axy\syspaths\errors`.

* `RequirePatterns` - `$patterns` not defined nor in the property, nor in the constructor.
* `PatternNotFound` - where access via `__get` or in a link form other pattern.
* `InvalidPattern` - not closed link, a nested array is not contains `root`, `__classname` in not exists and etc.
