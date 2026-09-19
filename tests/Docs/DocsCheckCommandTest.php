<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Docs;

use Svnjn\Standards\Process\ProcessResult;
use Svnjn\Standards\Tests\Support\DocsFixture;

it('reports success', function (): void {
    $f = new DocsFixture();
    $f->project->write('README.md', "```php\n\$a = 1;\n```\n");
    $f->processes->willReturn(new ProcessResult(0), new ProcessResult(0, '{"files": {}, "errors": []}'));

    expect($f->command->run([]))->toBe(0)
        ->and($f->output->lines())->toBe(['Docs check passed: 1 PHP example(s) are valid.']);
});

it('lists each problem and fails', function (): void {
    $f = new DocsFixture();
    $f->project->write('docs/usage.md', "```php\n\$a = ;\n```\n");
    $f->processes->willReturn(new ProcessResult(255, "PHP Parse error:  syntax error in x.php on line 2\n"));

    expect($f->command->run(['docs']))->toBe(1)
        ->and($f->output->errors())->toBe([
            'docs/usage.md:2  Parse error:  syntax error',
            '',
            '1 problem(s) in 1 PHP example(s). Fix the example, or put <!-- docs-check: skip --> on the line before a fragment that is not meant to run.',
        ]);
});

it('reports a crash as a failure', function (): void {
    $f = new DocsFixture();
    $f->project->write('README.md', "```php\n\$a = 1;\n```\n");
    $f->processes->willReturn(new ProcessResult(0), new ProcessResult(255, '', 'Boom'));

    expect($f->command->run([]))->toBe(1)
        ->and($f->output->errors()[0])->toContain('Boom');
});
