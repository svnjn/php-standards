# Decisions

Why svnjn packages are built the way they are. Read this before proposing to change a rule; the alternatives listed as rejected were considered on purpose.

## Platform

| Decision | Why |
|---|---|
| PHP 8.3 minimum, tested on 8.3–8.5 | 8.2 left security support at the end of 2026; Pest 4 needs 8.3. |
| Laravel 12 and 13 | Laravel has no LTS releases any more; these are the supported majors. Drop 12 after its security support ends (early 2027). |
| Pest `^4.0 \|\| ^5.0` | Pest 5 needs PHP 8.4 and Laravel 13. Allowing both lets Composer pick per environment, and CI runs both. Drop `^4.0` together with Laravel 12. |
| Filament 5 only | The current major, built on Livewire 4 like the dashboards. |

## Strictness

| Decision | Why |
|---|---|
| PHPStan at max, strict rules, bleeding edge, no baselines | Catch mistakes before they run. A baseline hides debt that never gets paid. |
| No key-value arrays or `mixed` in the public API (`svnjn.*` rules) | Callers can't know which keys an array has. Data objects, `list<T>` and enums are predictable, autocompleted and checked. Interface-dictated methods (`jsonSerialize()`, `toArray()`) are exempt. |
| Rector never makes a method private | Frameworks call some methods by name from their base class, like Livewire's `rules()`; a private one fails at runtime. The `strict` preset already stops new `protected` methods. |
| Bans enforced by tools, not review | Pest's `php`, `security` and `strict` presets plus the `svnjn` preset; custom PHPStan rules for what arch tests can't see (`@`, `global`, magic accessors, JSON flags); Pint for `DateTime`, which arch tests can't see either. |
| Rector downgrade guard | PHPStan parses with the PHP it runs on, so PHP 8.4+ syntax passed locally in 8.3 packages. Rector's downgrade set for the lowest version makes `composer lint` catch it. |
| Warnings fail tests only from the package's own code | Vendor deprecations can't be fixed by the package; they're reported, not fatal. |

## Data

| Decision | Why |
|---|---|
| `final readonly` data objects with `fromReader()` named constructors | Explicit, typed, immutable; the conversion from raw input happens once, at the boundary. |
| `ArrayReader`, copied into each package as public API | Runtime code can't come from a dev dependency. A reader (not `Assert` helpers) never hands out raw arrays and names the full path of a bad field. |

## Testing

| Decision | Why |
|---|---|
| Fakes, not mocks, shipped in `src/Testing` | Fakes test outcomes and survive refactors; shipped fakes let apps test against the package too. |
| PSR-20 clock, PSR-18 HTTP client, PSR-3 logger | The outside world becomes swappable without mocking. |
| Namespaced Pest files, no `$this->` state, typed fixtures and small typed helpers | Pest's `$this` properties and some of Laravel's, Livewire's and Filament's test helpers are untyped; tests should get the same checking as `src/`. |
| Thresholds: type coverage 100%, code coverage 90%, mutation 80% | High enough to mean something, low enough to reach without gaming. |

## Tooling and delivery

| Decision | Why |
|---|---|
| One shared package (`php-standards`) owns configs, rules and tool versions | Anything copied into every package drifts. Packages keep thin wrappers and update with `composer update`. |
| Public `php-standards` | Public packages, their contributors and their CI must be able to install it. |
| Reusable CI workflow | One place to change CI for every package. |
| Private packages run the full CI matrix only on pull requests, tags and manual runs | The organisation's free CI minutes are shared. Public repositories get free minutes and run it always. |
| Docs live in each package; PHP examples are linted and analysed | Docs are versioned with the code, and can't silently go stale. |
| Livewire for UIs; Filament plugins ship no CSS; dashboards commit `dist/` and serve it themselves | One PHP codebase under PHPStan. Apps never need Node or a publish step. |

## Rejected

| Rejected | Why |
|---|---|
| Valinor | Hand-written `fromReader()` is explicit and PHPStan-checked. A package mapping many large nested payloads may still add it. |
| `spaze/phpstan-disallowed-calls` | Covered by the arch presets and our own rules. |
| Mocks as the default | Tied to implementation details, weakly typed. Mockery stays available for the rare case. |
| A generated API reference site | Strict types and data objects make editor autocomplete the reference. |
| Docker | Local PHP is enough. |
| Vue or React UIs | A second codebase outside PHPStan. |
| Facades in packages | They hide dependencies behind `__callStatic`; inject services instead. |
| Shared PhpStorm run configurations | Their format couldn't be verified; the debugging guide explains the setup instead. |
