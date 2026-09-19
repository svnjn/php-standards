<?php

declare(strict_types=1);

namespace Svnjn\Standards\Docs;

final readonly class DocsProblem
{
    public function __construct(
        public string $file,
        public int $line,
        public string $message,
    ) {}
}
