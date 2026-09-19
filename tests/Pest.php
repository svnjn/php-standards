<?php

declare(strict_types=1);

use Svnjn\Standards\Pest\SvnjnPreset;
use Svnjn\Standards\Tests\Support\TemporaryDirectory;

SvnjnPreset::register();

// Pest binds hooks to the test case, so this closure can't be static.
pest()->use()->afterEach(function (): void {
    TemporaryDirectory::cleanUp();
})->in(__DIR__);
