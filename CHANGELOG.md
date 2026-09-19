# Changelog

All notable changes are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- Rector no longer makes methods private. Frameworks call some methods by name from their base class, like Livewire's `rules()`, and a private one failed at runtime.
- The docs checker only ever empties its own work directory (`build/docs-check`). Mutation testing could turn it into a delete of the whole project.

## [1.0.0] - 2026-09-19

### Added

- Base PHPStan config: max level, bleeding edge, extra strict options, PHP 8.3–8.5.
- Custom PHPStan rules: no key-value arrays or `mixed` from public methods and properties, no error suppression, no `global` or `$GLOBALS`, no magic accessors, `JSON_THROW_ON_ERROR` required.
- Shared Pint config and Rector set.
- `svnjn` Pest arch preset.
- `svnjn-docs-check`, `svnjn-link` and `svnjn-unlink` commands.
- Guidelines in `docs/guidelines`.
- Reusable `package-ci.yml` and `package-release.yml` workflows.
- `config/phpstan-laravel.neon`: Larastan's strict checks, `env()` allowed in the package's `config/`, schema from package, workbench and Testbench migrations.
- `SvnjnRector` rewrites syntax newer than the package's lowest PHP version (Rector's downgrade sets), so `composer lint` catches it.
