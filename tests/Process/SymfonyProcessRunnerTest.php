<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Process;

use Svnjn\Standards\Process\SymfonyProcessRunner;

it('captures output and the exit code', function (): void {
    $result = (new SymfonyProcessRunner())->run([PHP_BINARY, '-r', 'fwrite(STDOUT, "out"); fwrite(STDERR, "err"); exit(3);'], __DIR__);

    expect($result->exitCode)->toBe(3)
        ->and($result->successful())->toBeFalse()
        ->and($result->output)->toBe('out')
        ->and($result->errorOutput)->toBe('err');
});

it('streams a command and returns its exit code', function (): void {
    expect((new SymfonyProcessRunner())->stream([PHP_BINARY, '-r', 'exit(4);'], __DIR__))->toBe(4);
});
