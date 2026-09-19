<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Fixtures\Arch\UsesCompact;

final class Example
{
    public function run(): void
    {
        $a = 1; compact('a');
    }
}
