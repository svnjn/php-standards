<?php

declare(strict_types=1);

namespace Svnjn\Standards\Process;

use Symfony\Component\Process\Process;

final class SymfonyProcessRunner implements ProcessRunner
{
    public function run(array $command, string $workingDirectory): ProcessResult
    {
        $process = new Process($command, $workingDirectory, timeout: null);
        $process->run();

        return new ProcessResult(
            $process->getExitCode() ?? 1,
            $process->getOutput(),
            $process->getErrorOutput(),
        );
    }

    public function stream(array $command, string $workingDirectory): int
    {
        $process = new Process($command, $workingDirectory, timeout: null);

        if (Process::isTtySupported()) {
            $process->setTty(true);
        }

        return $process->run(static function (string $type, string $buffer): void {
            fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
        });
    }
}
