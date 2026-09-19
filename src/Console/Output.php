<?php

declare(strict_types=1);

namespace Svnjn\Standards\Console;

interface Output
{
    public function line(string $message = ''): void;

    public function error(string $message): void;
}
