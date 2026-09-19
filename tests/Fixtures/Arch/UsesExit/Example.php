<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Fixtures\Arch\UsesExit;

final class Example
{
    public function run(): void
    {
        exit(1);
    }
}
