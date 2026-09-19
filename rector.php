<?php

declare(strict_types=1);

use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Svnjn\Standards\Rector\SvnjnRector;

return SvnjnRector::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSkip([
        __DIR__ . '/tests/Fixtures',
        __DIR__ . '/tests/PHPStan/Rules/data',
        // Carbon\Carbon is named, not loaded: packages without Carbon must not autoload it.
        StringClassNameToClassConstantRector::class => [
            __DIR__ . '/src/Pest/SvnjnPreset.php',
            __DIR__ . '/tests/Pest/SvnjnPresetTest.php',
        ],
    ]);
