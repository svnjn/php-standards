<?php

declare(strict_types=1);

namespace Svnjn\Standards\Testing;

use Svnjn\Standards\Console\Output;

/**
 * In-memory Output for tests.
 */
final class BufferedOutput implements Output
{
    /** @var list<string> */
    private array $lines = [];

    /** @var list<string> */
    private array $errors = [];

    public function line(string $message = ''): void
    {
        $this->lines[] = $message;
    }

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * @return list<string>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
