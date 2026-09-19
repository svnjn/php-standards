<?php

declare(strict_types=1);

namespace Svnjn\Standards\Process;

interface ProcessRunner
{
    /**
     * Runs the command and captures its output.
     *
     * @param  list<string>  $command
     */
    public function run(array $command, string $workingDirectory): ProcessResult;

    /**
     * Runs the command with its output passed straight through to the terminal.
     *
     * @param  list<string>  $command
     */
    public function stream(array $command, string $workingDirectory): int;
}
