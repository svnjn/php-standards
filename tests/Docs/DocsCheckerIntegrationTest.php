<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Docs;

use Svnjn\Standards\Docs\DocsChecker;
use Svnjn\Standards\Process\SymfonyProcessRunner;

// Runs the real `php -l` and PHPStan against the fixtures in tests/Fixtures/Docs.

function realChecker(): DocsChecker
{
    return new DocsChecker(new SymfonyProcessRunner(), dirname(__DIR__, 2));
}

it('passes valid examples and skips marked fragments', function (): void {
    $report = realChecker()->check(['tests/Fixtures/Docs/valid.md']);

    expect($report->problems)->toBe([])
        ->and($report->checkedExamples)->toBe(1);
});

it('reports a renamed method at its markdown line', function (): void {
    $problems = realChecker()->check(['tests/Fixtures/Docs/unknown-method.md'])->problems;

    expect($problems)->toHaveCount(1)
        ->and($problems[0]->file)->toBe('tests/Fixtures/Docs/unknown-method.md')
        ->and($problems[0]->line)->toBe(7)
        ->and($problems[0]->message)->toContain('renamedMethod');
});

it('reports a syntax error at its markdown line', function (): void {
    $problems = realChecker()->check(['tests/Fixtures/Docs/syntax-error.md'])->problems;

    expect($problems)->toHaveCount(1)
        ->and($problems[0]->line)->toBe(4)
        ->and($problems[0]->message)->toContain('syntax error');
});
