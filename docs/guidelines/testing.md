# Testing

Tests use [Pest](https://pestphp.com). Packages allow Pest 4 and 5 (`^4.0 || ^5.0`); Composer picks the one your PHP and Laravel versions support, and CI runs both, so stick to Pest's own API.

## Layout

| Folder | Contains |
|---|---|
| `tests/Unit` | Plain PHP tests. Most tests live here. |
| `tests/Feature` | Tests that boot Laravel through Testbench (Laravel packages). |
| `tests/Browser` | Pest browser tests (UI packages). |
| `tests/Arch` | Architecture rules. |
| `tests/Support` | Typed fixtures and helpers shared by tests. |

## Writing a test

```php
namespace Svnjn\Invoices\Tests\Unit;

use Svnjn\Invoices\Testing\InMemoryInvoiceRepository;

it('issues an invoice for an order', function (): void {
    $repository = new InMemoryInvoiceRepository();
    $service = new InvoiceService($repository);

    $invoice = $service->issue(OrderFactory::make(total: 1250));

    expect($invoice->total->cents)->toBe(1250)
        ->and($repository->all())->toHaveCount(1);
});
```

Three conventions keep tests typed, so PHPStan checks them and your editor autocompletes them:

1. **Give each test file a namespace.** Helper functions in different files can't collide, and tests may use the package's `@internal` classes.
2. **No `$this->something` state.** Pest's `$this` properties are untyped. Build what the test needs inside it, or use a typed fixture object:

   ```php
   // tests/Support/BillingFixture.php
   final readonly class BillingFixture
   {
       public InMemoryInvoiceRepository $invoices;
       public FrozenClock $clock;
       public InvoiceService $service;

       public function __construct()
       {
           $this->invoices = new InMemoryInvoiceRepository();
           $this->clock = new FrozenClock(new DateTimeImmutable('2026-01-01'));
           $this->service = new InvoiceService($this->invoices, $this->clock);
       }
   }

   it('dates invoices today', function (): void {
       $f = new BillingFixture();

       expect($f->service->issue(OrderFactory::make())->issuedAt)->toEqual($f->clock->now());
   });
   ```

3. **Type closure parameters and returns**, including datasets: `function (string $input, int $expected): void`.

## Fakes, not mocks

A mock checks *how* your code works (which methods it called); a fake is a small working implementation, so the test checks *what* your code produced. Refactors don't break fake-based tests, and fakes are ordinary typed classes.

- Every interface in `Contracts\` that talks to the outside world gets an in-memory fake in `src/Testing/`.
- `src/Testing/` ships with the package, so apps can test their own code against your fakes (like Laravel's `Mail::fake()`).
- Production code never uses `Testing\` — an arch test enforces it.
- Mockery is installed for the rare case a fake can't express (simulating one specific failure). Reach for it last.

```php
final class InMemoryInvoiceRepository implements InvoiceRepository
{
    /** @var array<string, Invoice> */
    private array $invoices = [];

    public function save(Invoice $invoice): void
    {
        $this->invoices[$invoice->id->value] = $invoice;
    }

    public function find(InvoiceId $id): ?Invoice
    {
        return $this->invoices[$id->value] ?? null;
    }

    /**
     * @return list<Invoice>
     */
    public function all(): array
    {
        return array_values($this->invoices);
    }
}
```

## The outside world through PSR interfaces

Core code depends on standard interfaces, so tests swap in a fake:

| Need | Depend on | In tests |
|---|---|---|
| Current time | `Psr\Clock\ClockInterface` (PSR-20) | A frozen clock |
| HTTP calls | `Psr\Http\Client\ClientInterface` (PSR-18) | A fake client with queued responses |
| Logging | `Psr\Log\LoggerInterface` (PSR-3) | An in-memory logger you can assert on |

Never call `new DateTimeImmutable()` for "now" or `time()` in `src/` — ask the clock.

## Test data factories

Each data object that tests build often gets a factory in `src/Testing/Factories/` with deterministic defaults (no Faker). Tests set only the fields they care about:

```php
final class InvoiceFactory
{
    public static function make(
        ?InvoiceId $id = null,
        int $total = 1000,
        InvoiceStatus $status = InvoiceStatus::Open,
    ): Invoice {
        return new Invoice(
            id: $id ?? new InvoiceId('inv_1'),
            total: Money::ofCents($total),
            status: $status,
            issuedAt: new DateTimeImmutable('2026-01-01T00:00:00+00:00'),
            lines: [],
        );
    }
}

$paid = InvoiceFactory::make(status: InvoiceStatus::Paid);
```

## Architecture tests

`tests/Arch/ArchTest.php` applies Pest's `php`, `security` and `strict` presets plus the `svnjn` preset, and package-specific rules (exceptions implement the interface, the core doesn't depend on Laravel, `Testing\` stays out of production code). PHPStan skips `tests/Arch`, because Pest's arch API is untyped.

## Quality bars

| Check | Minimum | Command |
|---|---|---|
| Type coverage | 100% | `composer test:types` |
| Code coverage | 90% | `composer test:coverage` |
| Mutation score | 80% | `composer test:mutate` (slow; CI runs it on pull requests) |

The minimums live in the `scripts` of the package's `composer.json`. Raise them freely; lower them only with a reason in the pull request.

Mutation testing runs altered versions of your code for real. Code that deletes files must check the path it was given (for example, that it ends in the folder it owns) before deleting anything, or one altered path can delete the project.

## Warnings fail tests

`phpunit.xml` fails the run on any warning, notice or deprecation triggered by the package's own code. Deprecations inside vendor code are reported but don't fail the run — you can't fix them.

## Running tests

| Command | Runs |
|---|---|
| `composer test` | The whole suite, in parallel |
| `vendor/bin/pest --filter="issues an invoice"` | Matching tests |
| `vendor/bin/pest tests/Unit/InvoiceServiceTest.php` | One file |
| `->only()` on a test | Just that test (remove before committing) |
| `vendor/bin/pest --bail` | Stops at the first failure |
| `vendor/bin/pest --profile` | Lists the slowest tests |

See [Debugging](debugging.md) for stepping through a test with Xdebug.
