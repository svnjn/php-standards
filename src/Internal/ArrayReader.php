<?php

declare(strict_types=1);

namespace Svnjn\Standards\Internal;

use BackedEnum;
use DateTimeImmutable;
use Exception;
use JsonException;
use Svnjn\Standards\Exceptions\InvalidDataException;
use TypeError;

/**
 * Reads typed values out of decoded data (JSON, config, API responses).
 *
 * Raw arrays never leave this class: nested objects come back as readers,
 * and every failure names the full path of the bad field.
 *
 *     $input = ArrayReader::fromJson($json);
 *     $name = $input->object('customer')->string('name');
 *     $lines = array_map(Line::fromReader(...), $input->list('lines'));
 *
 * @internal
 */
final readonly class ArrayReader
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    private function __construct(
        private array $data,
        private string $path,
    ) {}

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function from(array $data): self
    {
        return new self($data, '');
    }

    public static function fromJson(string $json): self
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidDataException('Invalid JSON: ' . $exception->getMessage(), $exception->getCode(), previous: $exception);
        }

        if (! is_array($data)) {
            throw new InvalidDataException(sprintf('JSON must contain an object, %s given.', get_debug_type($data)));
        }

        return new self($data, '');
    }

    /**
     * True when the field exists and is not null.
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_map(strval(...), array_keys($this->data));
    }

    public function string(string $key): string
    {
        $value = $this->required($key);

        return is_string($value) ? $value : throw $this->invalid($key, 'string', $value);
    }

    public function optionalString(string $key): ?string
    {
        return $this->has($key) ? $this->string($key) : null;
    }

    public function int(string $key): int
    {
        $value = $this->required($key);

        return is_int($value) ? $value : throw $this->invalid($key, 'int', $value);
    }

    public function optionalInt(string $key): ?int
    {
        return $this->has($key) ? $this->int($key) : null;
    }

    public function float(string $key): float
    {
        $value = $this->required($key);

        return is_float($value) || is_int($value) ? (float) $value : throw $this->invalid($key, 'float', $value);
    }

    public function optionalFloat(string $key): ?float
    {
        return $this->has($key) ? $this->float($key) : null;
    }

    public function bool(string $key): bool
    {
        $value = $this->required($key);

        return is_bool($value) ? $value : throw $this->invalid($key, 'bool', $value);
    }

    public function optionalBool(string $key): ?bool
    {
        return $this->has($key) ? $this->bool($key) : null;
    }

    /**
     * @template T of BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return T
     */
    public function enum(string $key, string $enum): BackedEnum
    {
        $value = $this->required($key);

        try {
            $case = is_int($value) || is_string($value) ? $enum::tryFrom($value) : null;
        } catch (TypeError) {
            $case = null;
        }

        if ($case !== null) {
            return $case;
        }

        $allowed = implode(', ', array_map(static fn(BackedEnum $case): string => (string) $case->value, $enum::cases()));

        throw is_int($value) || is_string($value)
            ? new InvalidDataException(sprintf('Field "%s" must be one of: %s.', $this->pathTo($key), $allowed))
            : $this->invalid($key, 'one of: ' . $allowed, $value);
    }

    public function dateTime(string $key): DateTimeImmutable
    {
        $value = $this->string($key);

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            throw $this->invalid($key, 'a date-time string', $value);
        }
    }

    public function optionalDateTime(string $key): ?DateTimeImmutable
    {
        return $this->has($key) ? $this->dateTime($key) : null;
    }

    public function object(string $key): self
    {
        $value = $this->required($key);

        if (! is_array($value) || ($value !== [] && array_is_list($value))) {
            throw $this->invalid($key, 'object', $value);
        }

        return new self($value, $this->pathTo($key));
    }

    public function optionalObject(string $key): ?self
    {
        return $this->has($key) ? $this->object($key) : null;
    }

    /**
     * A list of nested objects.
     *
     * @return list<self>
     */
    public function list(string $key): array
    {
        $readers = [];

        foreach ($this->listItems($key) as $index => $item) {
            $path = $this->pathTo($key) . '.' . $index;

            if (! is_array($item)) {
                throw new InvalidDataException(sprintf('Field "%s" must be object, %s given.', $path, get_debug_type($item)));
            }

            $readers[] = new self($item, $path);
        }

        return $readers;
    }

    /**
     * @return list<string>
     */
    public function strings(string $key): array
    {
        $strings = [];

        foreach ($this->listItems($key) as $index => $item) {
            $strings[] = is_string($item) ? $item : throw $this->invalid($key . '.' . $index, 'string', $item);
        }

        return $strings;
    }

    /**
     * @return list<int>
     */
    public function ints(string $key): array
    {
        $ints = [];

        foreach ($this->listItems($key) as $index => $item) {
            $ints[] = is_int($item) ? $item : throw $this->invalid($key . '.' . $index, 'int', $item);
        }

        return $ints;
    }

    /**
     * @return list<mixed>
     */
    private function listItems(string $key): array
    {
        $value = $this->required($key);

        if (! is_array($value) || ! array_is_list($value)) {
            throw $this->invalid($key, 'list', $value);
        }

        return $value;
    }

    private function required(string $key): mixed
    {
        if (! array_key_exists($key, $this->data)) {
            throw new InvalidDataException(sprintf('Field "%s" is missing.', $this->pathTo($key)));
        }

        return $this->data[$key];
    }

    private function invalid(string $key, string $expected, mixed $value): InvalidDataException
    {
        return new InvalidDataException(sprintf(
            'Field "%s" must be %s, %s given.',
            $this->pathTo($key),
            $expected,
            get_debug_type($value),
        ));
    }

    private function pathTo(string $key): string
    {
        return $this->path === '' ? $key : $this->path . '.' . $key;
    }
}
