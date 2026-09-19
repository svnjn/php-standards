# Contributing

Thanks for helping. Changes here reach every svnjn package, so they need care.

## Setup

```bash
composer install
composer check
```

`composer check` runs everything CI runs except mutation testing (`composer test:mutate`).

## Changing a rule

- A rule that can make an existing, passing package fail is a **breaking change**: it ships in a major release.
- Every PHPStan rule has a `RuleTestCase` in `tests/PHPStan/Rules` with a fixture in `tests/PHPStan/Rules/data`, covering what it reports and what it allows.
- Explain the rule, and the pattern to use instead, in `docs/guidelines/`.

## Releasing

1. Move the `## [Unreleased]` entries under a new `## [x.y.z] - YYYY-MM-DD` heading in `CHANGELOG.md`.
2. Tag the version and push it; the release workflow publishes the GitHub release from the changelog:

   ```bash
   git tag v1.4.0 && git push origin v1.4.0
   ```

3. Move the major tag, so packages calling the shared workflows with `@v1` get the new version:

   ```bash
   git tag -f v1 && git push -f origin v1
   ```

## Pull requests

- One change per pull request.
- Add a line to `CHANGELOG.md` under `## [Unreleased]`.
- Follow the [guidelines](docs/guidelines/README.md) — this package holds itself to them.
