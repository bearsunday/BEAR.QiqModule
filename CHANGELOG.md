# Changelog

All notable changes to this project will be documented in this file.

## [2.1.2] - 2026-09-04

### Fixed

- A compile no longer fails with `Unbound qiq_paths` where `QiqProdModule` is installed and `QiqModule` is not, as in `prod-cli-hal-app`: the compile step has no templates and answers 0

## [2.1.1] - 2026-09-03

### Fixed

- `QiqErrorPage` receives the `error_page` renderer: the qualifier moved to the parameter, where Ray.Di 2.23 reads it
- The distribution ships the `var/qiq` skeleton the manual tells users to copy, and no longer ships `demo/`

## [2.1.0] - 2026-08-27

### Added

- `QiqCompileStep` compiles every template into `{buildDir}/qiq` during the build, so a read-only tree can serve

### Changed

- `QiqProdModule()` without a cache path binds a read-only `Catalog` reading `{buildDir}/qiq`, so prod ships no template tree, works inside a phar, and raises `TemplateNotCompiledException` until the compile step has run
- Require PHP 8.2+ (from PHP 8.1), the floor of the `bear/sunday` that carries `CompileStepInterface`
- `QiqErrorPageRenderer` renders with the injected `Template` instead of `Template::new()`

### Deprecated

- `QiqProdModule($cachePath)` keeps compiling at serve time into the path, unchanged; the parameter goes away in 3.0

### Migration Guide

Existing prod modules keep working as before, with a deprecation notice. To serve the
compiled build instead, compile the application before serving it and drop the cache
path so the read side looks where the step wrote:

```php
// Before
$this->install(new QiqProdModule($this->appMeta->appDir . '/var/tmp/cache/qiq'));

// After: templates are read from the build directory the compile step filled
$this->install(new QiqProdModule());
```

Delete the existing `var/build` and compile again rather than compiling on top of it. A `Meta`
baked into DI scripts from before `bear/app-meta` 1.13 carries no `$buildDir`, and it is read
while the container is built, so the application stops answering at boot instead of losing a
single template.

The argument-less form needs the `bear/package` release that runs compile steps.

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
