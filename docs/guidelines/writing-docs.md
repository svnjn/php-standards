# Writing docs

## Where docs live

Docs live in the package, next to the code, so each version's docs match that version.

| File | Contains |
|---|---|
| `README.md` | What the package does, installation, one quick example, links to `docs/`. Keep it to one screen. |
| `docs/installation.md` | Requirements, installing (public or private), publishing config. |
| `docs/configuration.md` | Every option, its default and what it changes. |
| `docs/usage.md` | Tasks, one heading each: "Issue an invoice", "Refund a payment". |
| `docs/testing.md` | How apps test code that uses the package — its fakes and factories. |
| `docs/upgrading.md` | What to change for each major version. |
| `CHANGELOG.md` | Every release, in [Keep a Changelog](https://keepachangelog.com) format. |

## Examples are checked

`composer docs` (part of `composer check` and CI) finds every ` ```php ` block in `README.md` and `docs/`, and:

1. runs `php -l` on it, so syntax errors fail;
2. runs PHPStan at level 5 over all of them, so a renamed class, method or argument fails.

Failures point at the markdown line: `docs/usage.md:42  Call to an undefined method Svnjn\Invoices\InvoiceService::create().`

Variables an example doesn't define are allowed, so examples stay short. To have calls on such a variable checked, declare its type:

```php
/** @var \Svnjn\Invoices\InvoiceService $invoices */
$invoice = $invoices->issue($order);
```

For a fragment that isn't meant to run — a method body shown on its own, a diff, pseudo-code — put the skip marker on the line directly before the fence:

````markdown
<!-- docs-check: skip -->
```php
public function handle(): void { /* ... */ }
```
````

## When the public API changes

The pull request updates, in the same change:

- the docs pages that mention it,
- `CHANGELOG.md` under `## [Unreleased]`,
- `docs/upgrading.md` if it breaks anything.

The pull request template asks for all three.

## Style

- Lead with the task, not the concept. "Issue an invoice", then the code, then the details.
- One example per task, as short as it can be while still running.
- Plain words. If a sentence needs a second read, split it.
