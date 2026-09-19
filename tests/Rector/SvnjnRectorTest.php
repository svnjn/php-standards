<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Rector;

use Rector\Configuration\RectorConfigBuilder;
use Svnjn\Standards\Process\SymfonyProcessRunner;
use Svnjn\Standards\Rector\SvnjnRector;
use Svnjn\Standards\Tests\Support\TemporaryDirectory;

function composerJsonRequiring(string $php): string
{
    return TemporaryDirectory::create()->write('composer.json', json_encode(['require' => ['php' => $php]], JSON_THROW_ON_ERROR));
}

it('returns a builder packages can add their paths to', function (): void {
    expect(SvnjnRector::configure()->withPaths([__DIR__]))->toBeInstanceOf(RectorConfigBuilder::class);
});

it('configures a package whose lowest PHP is the newest, without downgrades', function (): void {
    expect(SvnjnRector::configure(composerJsonRequiring('^8.5')))->toBeInstanceOf(RectorConfigBuilder::class);
});

it('finds the lowest PHP version a package allows', function (string $constraint, string $lowest): void {
    expect(SvnjnRector::lowestPhpVersion(composerJsonRequiring($constraint)))->toBe($lowest);
})->with([
    'caret' => ['^8.3', '8.3'],
    'range' => ['>=8.2 <9.0', '8.2'],
    'alternatives' => ['^8.4 || ^8.3', '8.3'],
    'patch' => ['^8.3.4', '8.3'],
]);

it('finds nothing without a usable constraint', function (): void {
    $directory = TemporaryDirectory::create();

    expect(SvnjnRector::lowestPhpVersion($directory->path . '/missing.json'))->toBeNull()
        ->and(SvnjnRector::lowestPhpVersion($directory->write('composer.json', '{"require": {}}')))->toBeNull()
        ->and(SvnjnRector::lowestPhpVersion($directory->write('composer.json', '{"require": {"php": "*"}}')))->toBeNull();
});

it('never makes private a method a framework calls by name', function (): void {
    // Runs the real Rector on a Livewire-like component whose base class calls rules().
    $result = (new SymfonyProcessRunner())->run([
        PHP_BINARY, 'vendor/bin/rector', 'process', 'tests/Fixtures/Rector/FrameworkHook.php',
        '--config', 'tests/Fixtures/Rector/rector.php', '--dry-run', '--clear-cache', '--no-progress-bar',
    ], dirname(__DIR__, 2));

    expect($result->output)->not->toContain('private function rules')
        ->and($result->exitCode)->toBe(0);
});
