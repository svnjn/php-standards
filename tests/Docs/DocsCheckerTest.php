<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Docs;

use Svnjn\Standards\Docs\DocsProblem;
use Svnjn\Standards\Docs\DocsReport;
use Svnjn\Standards\Exceptions\CommandFailedException;
use Svnjn\Standards\Process\ProcessResult;
use Svnjn\Standards\Tests\Support\DocsFixture;

it('passes when there are no php examples', function (): void {
    $f = new DocsFixture();
    $f->project->write('README.md', "# Title\n\n```bash\ncomposer test\n```\n");

    $report = $f->checker->check(['README.md', 'docs']);

    expect($report->passed())->toBeTrue()
        ->and($report->checkedExamples)->toBe(0)
        ->and($f->processes->recorded())->toBe([]);
});

it('lints each example and analyses them together', function (): void {
    $f = new DocsFixture();
    $f->project->write('README.md', "```php\n\$a = 1;\n```\n");
    $f->project->write('docs/usage.md', "```php\n<?php\n\$b = 2;\n```\n");
    $f->processes->willReturn(
        new ProcessResult(0),
        new ProcessResult(0),
        new ProcessResult(0, '{"totals": {"errors": 0, "file_errors": 0}, "files": {}, "errors": []}'),
    );

    $report = $f->checker->check(['README.md', 'docs']);
    $config = $f->project->path . '/build/docs-check/phpstan.neon';

    expect($report->passed())->toBeTrue()
        ->and($report->checkedExamples)->toBe(2)
        ->and($f->processes->commandLines())->toBe([
            PHP_BINARY . ' -d display_errors=stderr -d log_errors=0 -l ' . $f->snippet(1),
            PHP_BINARY . ' -d display_errors=stderr -d log_errors=0 -l ' . $f->snippet(2),
            PHP_BINARY . " {$f->project->path}/vendor/bin/phpstan analyse --no-progress --no-interaction --error-format=json --memory-limit=1G --configuration={$config}",
        ])
        ->and(file_get_contents($f->snippet(1)))->toBe("<?php\n\$a = 1;")
        ->and(file_get_contents($f->snippet(2)))->toBe("<?php\n\$b = 2;");
});

it('maps syntax errors back to the markdown line', function (): void {
    $f = new DocsFixture();
    $f->project->write('docs/usage.md', "Intro\n\n```php\n\$a = 1;\n\$b = ;\n```\n");
    $f->processes->willReturn(new ProcessResult(255, "PHP Parse error:  syntax error, unexpected token \";\" in {$f->snippet(1)} on line 3\nErrors parsing {$f->snippet(1)}\n"));

    $report = $f->checker->check(['docs']);

    expect($report->problems)->toEqual([
        new DocsProblem('docs/usage.md', 5, 'Parse error:  syntax error, unexpected token ";"'),
    ]);
});

it('finds the error when php -l prints its summary first', function (): void {
    $f = new DocsFixture();
    $f->project->write('docs/usage.md', "```php\n\$a = 1;\n\$b = ;\n```\n");
    $f->processes->willReturn(new ProcessResult(255, "Errors parsing {$f->snippet(1)}\n", "\nParse error: syntax error, unexpected token \";\" in {$f->snippet(1)} on line 3\n"));

    $report = $f->checker->check(['docs']);

    expect($report->problems)->toEqual([
        new DocsProblem('docs/usage.md', 3, 'Parse error: syntax error, unexpected token ";"'),
    ]);
});

it('falls back to the first line when php -l names no error', function (): void {
    $f = new DocsFixture();
    $f->project->write('docs/usage.md', "```php\n\$a = 1;\n```\n");
    $f->processes->willReturn(new ProcessResult(255, "Could not open input file\n"));

    expect($f->checker->check(['docs'])->problems)->toEqual([
        new DocsProblem('docs/usage.md', 2, 'Could not open input file'),
    ]);
});

it('maps phpstan errors back to the markdown line', function (): void {
    $f = new DocsFixture();
    $f->project->write('docs/usage.md', "Intro\n\n```php\n\$reader->string('id');\n\$reader->missing();\n```\n");
    $f->processes->willReturn(new ProcessResult(0), new ProcessResult(1, json_encode([
        'totals' => ['errors' => 0, 'file_errors' => 1],
        'files' => [$f->snippet(1) => ['errors' => 1, 'messages' => [['message' => 'Call to an undefined method Reader::missing().', 'line' => 3]]]],
        'errors' => ['Config problem.'],
    ], JSON_THROW_ON_ERROR)));

    $report = $f->checker->check(['docs']);

    expect($report->problems)->toEqual([
        new DocsProblem('docs/usage.md', 5, 'Call to an undefined method Reader::missing().'),
        new DocsProblem('phpstan', 0, 'Config problem.'),
    ]);
});

it('fails loudly when phpstan crashes', function (): void {
    $f = new DocsFixture();
    $f->project->write('README.md', "```php\n\$a = 1;\n```\n");
    $f->processes->willReturn(new ProcessResult(0), new ProcessResult(255, '', 'Out of memory'));

    expect(fn(): DocsReport => $f->checker->check(['README.md']))
        ->toThrow(CommandFailedException::class, 'Out of memory');
});

it('fails loudly when phpstan output is not JSON', function (): void {
    $f = new DocsFixture();
    $f->project->write('README.md', "```php\n\$a = 1;\n```\n");
    $f->processes->willReturn(new ProcessResult(0), new ProcessResult(1, 'not json'));

    expect(fn(): DocsReport => $f->checker->check(['README.md']))
        ->toThrow(CommandFailedException::class, 'Invalid JSON');
});

it('clears leftovers from earlier runs, including folders', function (): void {
    $f = new DocsFixture();
    $f->project->write('README.md', "```php\n\$a = 1;\n```\n");
    $f->project->write('build/docs-check/old/example-999.php', '<?php');
    $f->processes->willReturn(new ProcessResult(0), new ProcessResult(0, '{"files": {}, "errors": []}'));

    $f->checker->check(['README.md']);

    expect(is_dir($f->project->path . '/build/docs-check/old'))->toBeFalse()
        ->and(is_file($f->snippet(1)))->toBeTrue();
});
