<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Pest;

use PHPUnit\Framework\ExpectationFailedException;
use Svnjn\Standards\Pest\SvnjnPreset;

function verifyPreset(string $namespace): void
{
    // Arch expectations verify themselves when they are destroyed.
    SvnjnPreset::expectations([$namespace]);
}

it('rejects banned functions and classes', function (string $fixture): void {
    expect(fn() => verifyPreset("Svnjn\\Standards\\Tests\\Fixtures\\Arch\\{$fixture}"))
        ->toThrow(ExpectationFailedException::class);
})->with([
    'dd()' => 'UsesDd',
    'compact()' => 'UsesCompact',
    'exit()' => 'UsesExit',
    'Carbon\Carbon' => 'UsesCarbon',
]);

it('accepts code that avoids them', function (): void {
    verifyPreset('Svnjn\Standards\Tests\Fixtures\Arch\Clean');

    expect(true)->toBeTrue();
});
