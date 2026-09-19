<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Fixtures\Arch\UsesCarbon;

final class Example
{
    public function run(): void
    {
        \Carbon\Carbon::now();
    }
}
