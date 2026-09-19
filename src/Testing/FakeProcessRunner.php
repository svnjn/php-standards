<?php

declare(strict_types=1);

namespace Svnjn\Standards\Testing;

use Svnjn\Standards\Process\ProcessResult;
use Svnjn\Standards\Process\ProcessRunner;

/**
 * Records every command instead of running it, and replies with scripted results.
 *
 * Unscripted commands succeed with no output.
 */
final class FakeProcessRunner implements ProcessRunner
{
    /** @var list<RecordedProcess> */
    private array $recorded = [];

    /** @var list<ProcessResult> */
    private array $results = [];

    public function willReturn(ProcessResult ...$results): self
    {
        $this->results = [...$this->results, ...array_values($results)];

        return $this;
    }

    public function run(array $command, string $workingDirectory): ProcessResult
    {
        $this->recorded[] = new RecordedProcess($command, $workingDirectory, streamed: false);

        return $this->nextResult();
    }

    public function stream(array $command, string $workingDirectory): int
    {
        $this->recorded[] = new RecordedProcess($command, $workingDirectory, streamed: true);

        return $this->nextResult()->exitCode;
    }

    /**
     * @return list<RecordedProcess>
     */
    public function recorded(): array
    {
        return $this->recorded;
    }

    /**
     * @return list<string>
     */
    public function commandLines(): array
    {
        return array_map(static fn(RecordedProcess $process): string => $process->commandLine(), $this->recorded);
    }

    private function nextResult(): ProcessResult
    {
        return array_shift($this->results) ?? new ProcessResult(0);
    }
}
