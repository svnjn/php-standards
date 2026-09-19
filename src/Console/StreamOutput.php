<?php

declare(strict_types=1);

namespace Svnjn\Standards\Console;

final class StreamOutput implements Output
{
    public function line(string $message = ''): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    public function error(string $message): void
    {
        fwrite(STDERR, $message . PHP_EOL);
    }
}
