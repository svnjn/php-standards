<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan;

final readonly class UnusedParameter
{
    public function __construct(
        public string $name,
        public int $line,
    ) {}
}
