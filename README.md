# svnjn/php-standards

Shared strictness for svnjn PHP packages: one dev dependency that brings the tools, their configuration, custom PHPStan rules, a Pest architecture preset, a docs checker, a local-linking helper, the guidelines, and the CI workflows.

Packages made with the svnjn package starter kit are already wired to it. This page is for wiring it by hand or understanding what it does.

## What's inside

| Piece | What it gives a package |
|---|---|
| Tool versions | PHPStan, strict and deprecation rules, Pint, Rector, Pest (4 or 5) with type coverage, var-dumper, all at versions that work together |
| `config/phpstan.neon` | PHPStan at max level, bleeding edge, extra strict options, and the custom rules |
| `config/phpstan-laravel.neon` | Larastan's stricter checks for Laravel packages |
| Custom PHPStan rules | No key-value arrays or `mixed` from public methods and properties; no unused parameters; no `@`, `global`, `$GLOBALS` or magic accessors; JSON calls must throw |
| `config/pint.json` | PER coding style plus strict types, final classes, strict comparisons, immutable dates |
| `SvnjnRector` | Rector sets for dead code, code quality, type declarations and early returns, up to the package's lowest PHP version — and a guard that rewrites syntax newer than that version |
| `SvnjnPreset` | A Pest arch preset banning `dd`, `compact`, `exit` and mutable Carbon |
| `svnjn-docs-check` | Lints and analyses every PHP example in the docs |
| `svnjn-link` / `svnjn-unlink` | Link a package into a local app for live testing |
| `docs/guidelines/` | How svnjn packages are written, and why |
| `.github/workflows/package-ci.yml` | The CI every package runs |
| `.github/workflows/package-release.yml` | GitHub releases from `CHANGELOG.md` |

## Installing

```bash
composer require --dev svnjn/php-standards
```

Allow its Composer plugins when asked (`pestphp/pest-plugin`, `phpstan/extension-installer`).

## Wiring

**`phpstan.neon`**

```neon
includes:
    - vendor/svnjn/php-standards/config/phpstan.neon

parameters:
    paths:
        - src
        - tests
```

**`pint.json`**

```json
{
    "extend": "vendor/svnjn/php-standards/config/pint.json"
}
```

**`rector.php`**

```php
use Svnjn\Standards\Rector\SvnjnRector;

return SvnjnRector::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests']);
```

**`tests/Pest.php`**

```php
use Svnjn\Standards\Pest\SvnjnPreset;

SvnjnPreset::register();
```

**`tests/Arch/ArchTest.php`**

<!-- docs-check: skip -->
```php
arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->strict();
arch()->preset()->svnjn();
```

**`.github/workflows/ci.yml`**

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
  workflow_dispatch:

jobs:
  ci:
    uses: svnjn/php-standards/.github/workflows/package-ci.yml@v1
    secrets: inherit
```

The workflow runs the package's Composer scripts `lint`, `analyse`, `docs`, `test`, `test:types`, `test:coverage` and `test:mutate`; see the starter kit's `composer.json` for their definitions.

## Guidelines

Start at [docs/guidelines](docs/guidelines/README.md). The guidelines ship inside the package, so a package's `AGENTS.md` can point AI tools at `vendor/svnjn/php-standards/docs/guidelines/`.

## Versioning

Semantic versioning. A release that can make an existing, passing package fail — a new rule, a stricter setting, a tool's major version — is a **major** release. Packages require `^1.0` and opt into stricter majors deliberately.

## Contributing

`composer check` must pass. See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

MIT. See [LICENSE.md](LICENSE.md).
