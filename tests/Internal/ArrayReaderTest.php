<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Internal;

use BackedEnum;
use Closure;
use DateTimeImmutable;
use Svnjn\Standards\Exceptions\InvalidDataException;
use Svnjn\Standards\Internal\ArrayReader;
use Svnjn\Standards\Tests\Support\InvoiceStatus;

it('reads typed scalar fields', function (): void {
    $input = ArrayReader::from(['id' => 'inv_1', 'total' => 1250, 'rate' => 0.2, 'paid' => true]);

    expect($input->string('id'))->toBe('inv_1')
        ->and($input->int('total'))->toBe(1250)
        ->and($input->float('rate'))->toBe(0.2)
        ->and($input->bool('paid'))->toBeTrue();
});

it('accepts an int where a float is expected', function (): void {
    expect(ArrayReader::from(['rate' => 1])->float('rate'))->toBe(1.0);
});

it('returns null for optional fields that are missing or null', function (): void {
    $input = ArrayReader::from(['note' => null]);

    expect($input->optionalString('note'))->toBeNull()
        ->and($input->optionalInt('missing'))->toBeNull()
        ->and($input->optionalFloat('missing'))->toBeNull()
        ->and($input->optionalBool('missing'))->toBeNull()
        ->and($input->optionalObject('missing'))->toBeNull()
        ->and($input->has('note'))->toBeFalse();
});

it('reads optional fields that are present', function (): void {
    $input = ArrayReader::from(['note' => 'hi', 'count' => 2, 'rate' => 1.5, 'paid' => false, 'meta' => ['a' => 'b']]);

    expect($input->optionalString('note'))->toBe('hi')
        ->and($input->optionalInt('count'))->toBe(2)
        ->and($input->optionalFloat('rate'))->toBe(1.5)
        ->and($input->optionalBool('paid'))->toBeFalse()
        ->and($input->optionalObject('meta')?->string('a'))->toBe('b');
});

it('names the field and the type it got', function (Closure $read, string $message): void {
    $input = ArrayReader::from(['id' => 42, 'total' => '12', 'rate' => 'x', 'paid' => 'yes', 'customer' => ['name' => 7], 'tags' => ['a', 3], 'ids' => [1, 'b']]);

    expect(fn() => $read($input))->toThrow(InvalidDataException::class, $message);
})->with([
    'string' => [fn(ArrayReader $input): string => $input->string('id'), 'Field "id" must be string, int given.'],
    'int' => [fn(ArrayReader $input): int => $input->int('total'), 'Field "total" must be int, string given.'],
    'float' => [fn(ArrayReader $input): float => $input->float('rate'), 'Field "rate" must be float, string given.'],
    'bool' => [fn(ArrayReader $input): bool => $input->bool('paid'), 'Field "paid" must be bool, string given.'],
    'nested' => [fn(ArrayReader $input): string => $input->object('customer')->string('name'), 'Field "customer.name" must be string, int given.'],
    'strings' => [fn(ArrayReader $input): array => $input->strings('tags'), 'Field "tags.1" must be string, int given.'],
    'ints' => [fn(ArrayReader $input): array => $input->ints('ids'), 'Field "ids.1" must be int, string given.'],
    'missing' => [fn(ArrayReader $input): string => $input->string('nope'), 'Field "nope" is missing.'],
    'object' => [fn(ArrayReader $input): ArrayReader => $input->object('tags'), 'Field "tags" must be object, array given.'],
    'list' => [fn(ArrayReader $input): array => $input->list('customer'), 'Field "customer" must be list, array given.'],
]);

it('reads nested objects and lists of objects with their paths', function (): void {
    $input = ArrayReader::from(['lines' => [['sku' => 'A'], ['sku' => 5]]]);
    $lines = $input->list('lines');

    expect($lines)->toHaveCount(2)
        ->and($lines[0]->string('sku'))->toBe('A')
        ->and(fn() => $lines[1]->string('sku'))->toThrow(InvalidDataException::class, 'Field "lines.1.sku" must be string, int given.');
});

it('rejects list items that are not objects', function (): void {
    expect(fn(): array => ArrayReader::from(['lines' => ['A']])->list('lines'))
        ->toThrow(InvalidDataException::class, 'Field "lines.0" must be object, string given.');
});

it('reads lists of scalars', function (): void {
    $input = ArrayReader::from(['tags' => ['a', 'b'], 'ids' => [1, 2]]);

    expect($input->strings('tags'))->toBe(['a', 'b'])
        ->and($input->ints('ids'))->toBe([1, 2]);
});

it('reads backed enums', function (): void {
    expect(ArrayReader::from(['status' => 'paid'])->enum('status', InvoiceStatus::class))->toBe(InvoiceStatus::Paid);
});

it('lists the allowed enum values when the value is unknown', function (mixed $value, string $message): void {
    expect(fn(): BackedEnum => ArrayReader::from(['status' => $value])->enum('status', InvoiceStatus::class))
        ->toThrow(InvalidDataException::class, $message);
})->with([
    'unknown string' => ['void', 'Field "status" must be one of: paid, open.'],
    'wrong scalar type' => [5, 'Field "status" must be one of: paid, open.'],
    'not a scalar' => [[1], 'Field "status" must be one of: paid, open, array given.'],
]);

it('reads date-times as immutable dates', function (): void {
    $date = ArrayReader::from(['issued_at' => '2026-09-19T10:00:00+00:00'])->dateTime('issued_at');

    expect($date->format(DATE_ATOM))->toBe('2026-09-19T10:00:00+00:00');
});

it('reads optional date-times', function (): void {
    $input = ArrayReader::from(['paid_at' => '2026-09-19T10:00:00+00:00', 'voided_at' => null]);

    expect($input->optionalDateTime('paid_at')?->format(DATE_ATOM))->toBe('2026-09-19T10:00:00+00:00')
        ->and($input->optionalDateTime('voided_at'))->toBeNull();
});

it('rejects values that are not date-times', function (): void {
    expect(fn(): DateTimeImmutable => ArrayReader::from(['issued_at' => 'someday'])->dateTime('issued_at'))
        ->toThrow(InvalidDataException::class, 'Field "issued_at" must be a date-time string, string given.');
});

it('decodes JSON objects', function (): void {
    $input = ArrayReader::fromJson('{"id": "inv_1", "customer": {}}');

    expect($input->string('id'))->toBe('inv_1')
        ->and($input->object('customer')->keys())->toBe([])
        ->and($input->keys())->toBe(['id', 'customer']);
});

it('rejects invalid JSON and JSON that is not an object', function (string $json, string $message): void {
    expect(fn(): ArrayReader => ArrayReader::fromJson($json))->toThrow(InvalidDataException::class, $message);
})->with([
    'invalid' => ['{"id":', 'Invalid JSON: Syntax error'],
    'scalar' => ['"text"', 'JSON must contain an object, string given.'],
]);
