# Data objects

Callers should know exactly what they get back. A key-value array doesn't tell them which keys exist or what type each one holds, so public methods return objects, enums, scalars, or lists of those — never `array<string, mixed>`, array shapes or `mixed`. PHPStan enforces this (`svnjn.arrayReturn`, `svnjn.arrayProperty`).

## Returning data

Use a `final readonly` class with promoted constructor properties:

```php
final readonly class Invoice
{
    /**
     * @param  list<InvoiceLine>  $lines
     */
    public function __construct(
        public InvoiceId $id,
        public Money $total,
        public InvoiceStatus $status,
        public DateTimeImmutable $issuedAt,
        public array $lines,
    ) {}
}
```

- Every field is typed and read-only; editors autocomplete it and PHPStan checks every access.
- A sequence is `list<T>` — allowed, because a list has no keys to guess.
- Fixed sets of values are backed enums, never string constants.

## Changing data

Data objects never change. Return a new one:

```php
public function markPaid(): self
{
    return new self($this->id, $this->total, InvoiceStatus::Paid, $this->issuedAt, $this->lines);
}
```

## Reading raw input

JSON, config and API responses arrive as arrays. Convert them at the boundary, once, with the package's `Support\ArrayReader`, in a named constructor:

```php
public static function fromReader(ArrayReader $input): self
{
    return new self(
        id: new InvoiceId($input->string('id')),
        total: Money::ofCents($input->int('total')),
        status: $input->enum('status', InvoiceStatus::class),
        issuedAt: $input->dateTime('issued_at'),
        lines: array_map(InvoiceLine::fromReader(...), $input->list('lines')),
    );
}

$invoice = Invoice::fromReader(ArrayReader::fromJson($body));
```

Bad input throws the package's `InvalidDataException` with the full path of the field: `Field "lines.2.sku" must be string, int given.`

The reader never hands out a raw array: nested objects come back as readers (`object()`, `list()`), and scalars as their type (`string()`, `optionalInt()`, `strings()`, `enum()`, `dateTime()`).

## Writing output

When callers need an array or JSON (an HTTP response, a queue payload), implement `JsonSerializable`. Its `jsonSerialize(): array` is exempt from the array rule because the interface dictates the signature:

```php
final readonly class Invoice implements JsonSerializable
{
    /**
     * @return array{id: string, total: int, status: string}
     */
    public function jsonSerialize(): array
    {
        return ['id' => $this->id->value, 'total' => $this->total->cents, 'status' => $this->status->value];
    }
}
```

Laravel's `Arrayable::toArray()` is exempt for the same reason.

## Collections with behaviour

When a list needs its own methods (totals, filtering, lookup by key), wrap it:

```php
/**
 * @implements IteratorAggregate<int, Invoice>
 */
final readonly class Invoices implements Countable, IteratorAggregate
{
    /**
     * @param  list<Invoice>  $items
     */
    public function __construct(
        private array $items,
    ) {}

    public function find(InvoiceId $id): ?Invoice
    {
        foreach ($this->items as $invoice) {
            if ($invoice->id->equals($id)) {
                return $invoice;
            }
        }

        return null;
    }

    public function total(): Money { /* ... */ }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
```

A lookup table (`array<string, Invoice>`) lives privately inside a class like this, never on the public API.

## Value objects

Wrap primitives that carry meaning — IDs, money, email addresses — in small readonly classes that validate on construction. `InvoiceId` can't be passed where a `CustomerId` is expected; a `string` can.
