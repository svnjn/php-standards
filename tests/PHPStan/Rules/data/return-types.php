<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules\Data\ReturnTypes;

use JsonSerializable;

final class Invoice {}

enum Status: string
{
    case Paid = 'paid';
}

final class Examples implements JsonSerializable
{
    /** @return array<string, int> */
    public function map(): array { return []; }

    /** @return array{id: int} */
    public function shape(): array { return ['id' => 1]; }

    public function plainArray(): array { return []; }

    public function anything(): mixed { return null; }

    /** @return list<Invoice> */
    public function invoices(): array { return []; }

    /** @return list<array<string, int>> */
    public function nested(): array { return []; }

    /** @return list<Invoice>|null */
    public function nullableList(): ?array { return null; }

    /** @return list{int, string} */
    public function tuple(): array { return [1, 'a']; }

    /** @return iterable<string, Invoice> */
    public function iterable(): iterable { return []; }

    protected function protectedMixed(): mixed { return null; }

    public function invoice(): Invoice { return new Invoice(); }

    public function status(): Status { return Status::Paid; }

    public function count(): int { return 0; }

    /** @return array<string, int> */
    private function privateMap(): array { return []; }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array { return []; }

    /** @return list<list<list<list<list<list<list<int>>>>>>> */
    public function tooDeepToInspect(): array { return []; }
}
