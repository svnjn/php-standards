<?php

declare(strict_types=1);

namespace Svnjn\Standards\Testing;

final readonly class RecordedProcess
{
    /**
     * @param  list<string>  $command
     */
    public function __construct(
        public array $command,
        public string $workingDirectory,
        public bool $streamed,
    ) {}

    public function commandLine(): string
    {
        return implode(' ', $this->command);
    }
}
