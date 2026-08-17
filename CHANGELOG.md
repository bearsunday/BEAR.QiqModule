# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added

- `QiqCompileStep` compiles every template into `{buildDir}/qiq` during the build, so a read-only tree can serve

### Changed

- BREAKING: `QiqProdModule` binds a read-only `Compiler`, so prod raises `TemplateNotCompiledException` until the compile step has run
- BREAKING: require PHP 8.2+ (from PHP 8.1), the floor of the `bear/sunday` that carries `CompileStepInterface`
- BREAKING: `QiqProdModule::__construct()` takes no cache path; the read side takes `AbstractAppMeta::$buildDir`
- `QiqErrorPageRenderer` renders with the injected `Template` instead of `Template::new()`

### Migration Guide

Existing prod modules keep resolving, but nothing compiles their templates any more.
Compile the application before serving it, and drop the cache path so the read side
looks where the step wrote:

```php
// Before
$this->install(new QiqProdModule($this->appMeta->appDir . '/var/tmp/cache/qiq'));

// After: templates are read from the build directory the compile step filled
$this->install(new QiqProdModule());
```

The argument-less form needs the `bear/package` release that runs compile steps.
Templates inside a phar stay unsupported: `Qiq\Catalog` reads the `phar:` scheme of a
template root as a collection name, so it never resolves one. A compiled DI script
carries the `appDir` of the machine that built it, so the build directory resolves
only where the application directory is the one the compile saw.

## [2.0.0] - 2024-12-28

### Changed

- Upgrade to Qiq 3.0 (from Qiq 1.x)
- Require PHP 8.1+ (from PHP 8.0)
- Template variable access changed from `$this->var` to `$var`
- Custom helpers now use `Qiq\Helpers` class instead of `HelperLocator`

### Removed

- `HelperLocator` class (use `Qiq\Helpers` instead)
- `HelperLocatorProvider` class
- `StubGen` class
- `LogicException` class (unused)
- `RuntimeException` class (unused)

### Migration Guide

#### Template Syntax

Update all templates to use direct variable access:

```php
// Before (Qiq 1.x)
{{h $this->name }}

// After (Qiq 3.x)
{{h $name }}
```

#### Custom Helpers

Create a custom `Helpers` class extending `HtmlHelpers`:

```php
use Qiq\Helper\Html\HtmlHelpers;

class MyHelpers extends HtmlHelpers
{
    public function myHelper(string $text): string
    {
        return $this->get(MyHelper::class)->__invoke($text);
    }
}
```

Bind it in your module:

```php
$this->bind(Helpers::class)->to(MyHelpers::class);
```

## [1.0.3] - 2023-03-21

### Fixed

- Fix HelperLocator for Qiq 1.1.0 compatibility

## [1.0.2] - 2022-12-15

### Fixed

- Remove non-existent bin configuration from composer.json
- Fixed PHP version specification in coding-standards.yml

## [1.0.1] - 2022-12-06

### Changed

- Bind Template class directly

## [1.0.0] - 2022-11-29

### Added

- Initial release
- QiqModule for BEAR.Sunday integration
- QiqRenderer for automatic template resolution
- QiqErrorModule for error page rendering
- HelperLocator with auto-loading support
