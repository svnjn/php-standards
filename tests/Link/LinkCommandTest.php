<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Link;

use Svnjn\Standards\Tests\Support\LinkFixture;

it('links and unlinks the given app', function (): void {
    $f = new LinkFixture();

    expect($f->command->link([$f->app]))->toBe(0)
        ->and($f->command->unlink([$f->app]))->toBe(0)
        ->and($f->output->errors())->toBe([]);
});

it('prints usage when the app path is missing', function (): void {
    $f = new LinkFixture();

    expect($f->command->link([]))->toBe(1)
        ->and($f->command->unlink(['a', 'b']))->toBe(1)
        ->and($f->output->errors())->toBe([
            'Usage: vendor/bin/svnjn-link <path-to-app>',
            'Usage: vendor/bin/svnjn-unlink <path-to-app>',
        ]);
});

it('prints the error and fails when linking fails', function (): void {
    $f = new LinkFixture();

    expect($f->command->link([$f->workspace->path . '/missing']))->toBe(1)
        ->and($f->output->errors()[0])->toContain('No composer.json found in the app directory');
});
