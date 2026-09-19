# Laravel

Laravel packages support Laravel 12 and 13. CI tests both, on every supported PHP version, with the oldest and newest allowed dependencies.

## Keep Laravel at the edge

The package's core — its services, data objects and contracts — is plain PHP. Laravel-specific code lives in `src/Laravel/`: the service provider, Artisan commands, jobs, middleware. An arch test fails if anything outside `src/Laravel` (or the UI folders) uses `Illuminate\`.

This keeps the core testable without booting Laravel, and splitting it into a framework-agnostic package later is a move, not a rewrite.

## The service provider

Package discovery registers it (`extra.laravel.providers` in `composer.json`).

- `register()` merges the config and binds services. Use `singletonIf()`/`bindIf()` for PSR interfaces (clock, HTTP client, logger), so an app's own binding wins.
- `boot()` publishes files and registers commands, only when running in the console.

Don't ship facades. Callers get the service through dependency injection, which is typed and visible; a facade hides it behind `__callStatic`.

## Config

- The package's config lives in `config/<name>.php` and is published with the tag `<name>-config`.
- `env()` is only called inside that config file (Larastan enforces it).
- Read options with typed accessors, never plain `config('...')`, which returns `mixed`:

```php
$currency = config()->string('invoices.currency');
$retries = config()->integer('invoices.retries');
$enabled = config()->boolean('invoices.enabled');
```

They throw if the option has the wrong type, so a bad `.env` fails loudly.

## Testing

Feature tests in `tests/Feature/` boot a Laravel app through [Testbench](https://packages.tools/testbench). `tests/Feature/TestCase.php` lists the package's service provider.

Use Pest's Laravel functions instead of `$this->...`, so tests stay typed:

```php
namespace Svnjn\Invoices\Tests\Feature;

use Svnjn\Invoices\Contracts\InvoiceRepository;
use Svnjn\Invoices\Testing\InMemoryInvoiceRepository;
use Svnjn\Invoices\Tests\Support\Console;

use function Pest\Laravel\get;

it('lists outstanding invoices', function (): void {
    app()->instance(InvoiceRepository::class, new InMemoryInvoiceRepository());

    Console::run('invoices:outstanding')->assertSuccessful();
});

it('shows the dashboard', function (): void {
    get('/invoices')->assertOk();
});
```

- Swap a dependency for its fake with `app()->instance(Contract::class, $fake)`.
- `Console::run()` (in `tests/Support`) wraps Pest's `artisan()`, whose `PendingCommand|int` return type can't be chained on.

## The workbench

`composer serve` builds and runs a real Laravel app with the package installed — Testbench's workbench:

| Where | What |
|---|---|
| `testbench.yaml` | Which providers load, migrations and seeders, the page that opens |
| `workbench/app/Providers/WorkbenchServiceProvider.php` | Demo bindings, e.g. fakes filled with sample data |
| `workbench/routes/web.php` | Demo pages |
| `workbench/database/seeders/DatabaseSeeder.php` | Demo data; signs up `test@example.com` / `password` |
| `workbench/storage/logs/laravel.log` | The demo app's log |

Run any Artisan command against the workbench with `vendor/bin/testbench`, for example `vendor/bin/testbench invoices:outstanding`. `composer build` rebuilds the database without starting the server.

## Static analysis

Laravel packages include `vendor/svnjn/php-standards/config/phpstan-laravel.neon` after the base config. It turns on Larastan's stricter checks (model properties against the schema, Octane compatibility, job and auth checks), lets `config/` call `env()`, and reads the schema from the package's, the workbench's and Testbench's migrations.

It also skips `svnjn.unusedParameter` in `src/Laravel`, `src/Filament`, `src/Dashboard` and `workbench/`. Laravel calls methods there by name and passes arguments whether or not they're needed — a notification's `via($notifiable)`, a policy's `viewAny($user)` — so an unused parameter is normal.
