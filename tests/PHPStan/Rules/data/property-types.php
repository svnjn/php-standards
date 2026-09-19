<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules\Data\PropertyTypes;

final class Invoice {}

final readonly class Data
{
    /**
     * @param array<string, int> $map
     * @param list<Invoice> $invoices
     * @param array<string, int> $secret
     */
    public function __construct(
        public array $map,
        public array $invoices,
        public mixed $anything,
        public int $count,
        private array $secret = [],
    ) {}
}

class Base
{
    /** @var array<string, int> */
    public array $items = [];
}

final class Child extends Base
{
    /** @var array<string, int> */
    public array $items = [];
}
