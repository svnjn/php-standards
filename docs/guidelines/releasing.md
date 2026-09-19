# Releasing

Packages follow [Semantic Versioning](https://semver.org): `MAJOR.MINOR.PATCH`.

## What counts as breaking

Anything a caller can depend on is public API:

- public classes, interfaces, methods and their signatures;
- data object fields and their types;
- enum cases;
- which exceptions are thrown, and their classes;
- config keys and their defaults (Laravel packages);
- the fakes and factories in `src/Testing/`.

Removing or changing any of these is a **major** release. Adding is **minor**. Fixing behaviour to match the docs is a **patch**.

Classes marked `@internal` (such as `Internal\SystemClock`) are not public API.

## Changelog

`CHANGELOG.md` follows [Keep a Changelog](https://keepachangelog.com). Every pull request adds its line under `## [Unreleased]`:

```markdown
## [Unreleased]

### Added
- `InvoiceService::refund()` for full and partial refunds.

### Fixed
- Totals no longer round half-cents down.
```

Sections: `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`.

## Cutting a release

1. Rename `## [Unreleased]` to `## [1.4.0] - 2026-09-19` and add a fresh empty `## [Unreleased]` above it.
2. For a major version, check `docs/upgrading.md` covers every breaking change.
3. Commit, then tag and push:

   ```bash
   git tag v1.4.0
   git push origin v1.4.0
   ```

The release workflow creates the GitHub release, using that version's section of `CHANGELOG.md` as the notes. It fails if the section is missing.

- **Public packages:** Packagist picks up the tag automatically.
- **Private packages:** apps install by tag straight from GitHub; see [Private packages](private-packages.md).

## Before 1.0

`0.x` versions may break in minor releases (`0.3.0` → `0.4.0`). Tag `1.0.0` once an app depends on the package in production.
