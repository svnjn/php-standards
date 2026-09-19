# AGENTS.md

Instructions for AI coding agents working on `svnjn/php-standards`.

## What this is

Shared tooling every svnjn package installs as a dev dependency: PHPStan config and custom rules (`src/PHPStan`), the Pest `svnjn` arch preset (`src/Pest`), the Rector set (`src/Rector`), the docs checker and local-link commands (`src/Docs`, `src/Link`, `bin/`), the guidelines (`docs/guidelines`) and the reusable CI workflows (`.github/workflows/package-*.yml`).

A change here reaches every package. A change that can make a passing package fail is a **major** release.

## Before you finish

Run `composer check`. It must pass.

## Rules

This package follows its own guidelines (`docs/guidelines/`), enforced by its own tools. In particular:

- Every PHPStan rule has a `RuleTestCase` in `tests/PHPStan/Rules` and a fixture in `tests/PHPStan/Rules/data` covering what it reports and what it allows.
- Tests are Pest, namespaced, with no `$this->` state; shared setup lives in typed fixtures in `tests/Support`.
- Update `docs/guidelines/` when a rule or pattern changes, and `CHANGELOG.md` under `## [Unreleased]`.
