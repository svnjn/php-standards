<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Link;

use Svnjn\Standards\Exceptions\CommandFailedException;
use Svnjn\Standards\Exceptions\InvalidArgumentException;
use Svnjn\Standards\Link\LinkedApp;
use Svnjn\Standards\Link\Linker;
use Svnjn\Standards\Link\LinkState;
use Svnjn\Standards\Process\ProcessResult;
use Svnjn\Standards\Tests\Support\LinkFixture;

it('links the package through a path repository', function (): void {
    $f = new LinkFixture();

    $f->linker->link($f->package, $f->app);

    expect($f->processes->commandLines())->toBe([
        'composer config repositories.svnjn-link-svnjn-demo {"type":"path","url":"' . $f->package . '","options":{"symlink":true}}',
        'composer require svnjn/demo:*@dev --with-dependencies',
    ])
        ->and($f->processes->recorded()[0]->workingDirectory)->toBe($f->app)
        ->and(LinkState::load($f->package)->apps)->toEqual([new LinkedApp($f->app, null, false)])
        ->and($f->output->lines()[0])->toStartWith('Linked svnjn/demo into');
});

it('remembers how the app required the package', function (): void {
    $f = new LinkFixture('{"require-dev": {"svnjn/demo": "^1.2"}}');

    $f->linker->link($f->package, $f->app);

    expect($f->processes->commandLines()[1])->toBe('composer require svnjn/demo:*@dev --with-dependencies --dev')
        ->and(LinkState::load($f->package)->apps)->toEqual([new LinkedApp($f->app, '^1.2', true)]);
});

it('does nothing when the app is already linked', function (): void {
    $f = new LinkFixture();

    $f->linker->link($f->package, $f->app);
    $f->linker->link($f->package, $f->app);

    expect($f->processes->recorded())->toHaveCount(2)
        ->and($f->output->lines()[1])->toBe("svnjn/demo is already linked into {$f->app}.");
});

it('restores the original constraint when unlinking', function (): void {
    $f = new LinkFixture('{"require": {"svnjn/demo": "^1.2"}}');
    $f->linker->link($f->package, $f->app);

    $f->linker->unlink($f->package, $f->app);

    expect(array_slice($f->processes->commandLines(), 2))->toBe([
        'composer config --unset repositories.svnjn-link-svnjn-demo',
        'composer require svnjn/demo:^1.2 --with-dependencies',
    ])
        ->and(is_dir($f->package . '/.svnjn'))->toBeFalse()
        ->and($f->output->lines()[1])->toBe("Unlinked svnjn/demo from {$f->app}.");
});

it('removes the package when the app did not require it before', function (): void {
    $f = new LinkFixture();
    $f->linker->link($f->package, $f->app);

    $f->linker->unlink($f->package, $f->app);

    expect($f->processes->commandLines()[3])->toBe('composer remove svnjn/demo');
});

it('says so when unlinking an app that is not linked', function (): void {
    $f = new LinkFixture();

    $f->linker->unlink($f->package, $f->app);

    expect($f->processes->recorded())->toBe([])
        ->and($f->output->lines())->toBe(["svnjn/demo is not linked into {$f->app}."]);
});

it('keeps no state when composer fails', function (): void {
    $f = new LinkFixture();
    $f->processes->willReturn(new ProcessResult(0), new ProcessResult(2));

    expect(fn() => $f->linker->link($f->package, $f->app))
        ->toThrow(CommandFailedException::class, 'failed with exit code 2')
        ->and(is_file($f->package . '/' . LinkState::FILE))->toBeFalse();
});

it('rejects directories without a composer.json', function (): void {
    $f = new LinkFixture();

    expect(fn() => $f->linker->link($f->package, $f->workspace->path . '/nowhere'))
        ->toThrow(InvalidArgumentException::class, 'No composer.json found in the app directory');
});

it('uses the configured composer binary', function (): void {
    $f = new LinkFixture();

    (new Linker($f->processes, $f->output, '/usr/local/bin/composer'))->link($f->package, $f->app);

    expect($f->processes->recorded()[0]->command[0])->toBe('/usr/local/bin/composer');
});
