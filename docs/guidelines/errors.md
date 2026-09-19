# Errors

## One interface per package

Every exception a package throws implements the package's exception interface, so callers can catch all of them in one place:

```php
namespace Svnjn\Invoices\Exceptions;

interface InvoicesException extends Throwable {}

final class InvoiceNotFound extends RuntimeException implements InvoicesException
{
    public static function withId(InvoiceId $id): self
    {
        return new self(sprintf('Invoice "%s" was not found.', $id->value));
    }
}
```

- Concrete exceptions are `final`, extend the closest SPL exception (`InvalidArgumentException`, `RuntimeException`, `UnexpectedValueException`, `LogicException`) and implement the interface.
- Named constructors (`withId()`, `forField()`) keep messages consistent.
- An arch test checks every class in `Exceptions\` implements the interface.

```php
try {
    $invoices->get($id);
} catch (InvoicesException $exception) {
    // anything the package can throw
}
```

## Throw, don't return false

A failure throws. It never comes back as `false`, `null`, `-1` or an error array.

`null` is only for "not there" when absence is normal. Name the two cases differently:

| Method | Missing record |
|---|---|
| `find(InvoiceId $id): ?Invoice` | returns `null` |
| `get(InvoiceId $id): Invoice` | throws `InvoiceNotFound` |

## PHP's own silent failures

- `json_decode()`/`json_encode()` always get `JSON_THROW_ON_ERROR` (enforced).
- No `@` to silence a warning (enforced). Check first (`is_file()`), or let it fail.
- Tests fail on any warning, notice or deprecation your code triggers.

## Document what's thrown

Add `@throws` to public methods whose exceptions are part of the contract:

```php
/**
 * @throws InvoiceNotFound
 */
public function get(InvoiceId $id): Invoice
```
