# Coding standards

## Principles

1. **Types everywhere.** `declare(strict_types=1)` in every file, every parameter, property and return typed. PHPStan runs at max level.
2. **Predictable data.** Public methods and properties never expose key-value arrays or `mixed`. See [Data objects](data-objects.md).
3. **Nothing hidden.** No magic methods, no `global`, no `@` error suppression, no `compact()`/`extract()`.
4. **Fail loudly.** Throw a package exception instead of returning `false` or `null` for an error. See [Errors](errors.md).
5. **Closed by default.** Classes are `final`; methods are `public` or `private` (no `protected`, no abstract classes). Open a class up only when extension is part of the design.

## Commands

| Command | What it does |
|---|---|
| `composer check` | Everything CI runs, except mutation testing. Run it before every push. |
| `composer fix` | Applies Rector, then Pint. Most style failures fix themselves. |
| `composer lint` | Pint and Rector in check mode. |
| `composer analyse` | PHPStan. |

## What enforces what

| Rule | Tool |
|---|---|
| `declare(strict_types=1)`, `final` classes, `===`, strict `in_array()`, `DateTimeImmutable` over `DateTime` | Pint (fixes it) |
| Modern syntax for the lowest supported PHP, dead code removal, early returns | Rector (fixes it) |
| Types, nullability, unreachable code, `mixed` leaks | PHPStan max + strict rules |
| No key-value arrays or `mixed` from public methods/properties | `svnjn.arrayReturn`, `svnjn.arrayProperty`, `svnjn.mixedReturn`, `svnjn.mixedProperty` |
| Every parameter used, unless a parent class or interface sets the signature | `svnjn.unusedParameter` (`composer fix` removes them from private methods) |
| No `@`, `global`, `$GLOBALS`, magic accessors | `svnjn.errorSuppression`, `svnjn.globalKeyword`, `svnjn.globalsVariable`, `svnjn.magicMethod` |
| `json_decode()`/`json_encode()` with `JSON_THROW_ON_ERROR` | `svnjn.jsonThrowOnError` |
| No `dd`, `dump`, `var_dump`, `die`, `exit`, `eval`, `extract`, `compact`, `Carbon\Carbon` in `src/` | Pest arch presets `php`, `security`, `strict`, `svnjn` |
| No protected methods, no abstract classes | Pest arch preset `strict` |

## When a rule is wrong for one line

It happens, usually at a boundary with a framework. Suppress **that line, that identifier**, and say why:

```php
// Livewire calls rules() by name and expects Laravel's validation array.
/** @phpstan-ignore svnjn.arrayReturn */
public function rules(): array
```

(Methods that implement an interface or override a parent are already exempt from the array rules and `svnjn.unusedParameter` — you don't need this for `jsonSerialize()`, `toArray()` or framework overrides. Laravel packages also skip `svnjn.unusedParameter` in `src/Laravel`, `src/Filament`, `src/Dashboard` and `workbench/`, where Laravel calls methods by name; see [Laravel](laravel.md#static-analysis).)

Never add a PHPStan baseline, never lower the level, never ignore by path in a package. If a rule keeps getting in the way, change it in `svnjn/php-standards` for everyone.

## PHP versions

Packages support PHP 8.3 and up. PHPStan and Rector read the lowest version from `composer.json`, so syntax newer than 8.3 (property hooks, asymmetric visibility) is reported even though you develop on a newer PHP.
