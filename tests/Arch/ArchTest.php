<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Arch;

use Svnjn\Standards\Exceptions\StandardsException;

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->strict();
arch()->preset()->svnjn();

arch('exceptions implement the package exception interface')
    ->expect('Svnjn\Standards\Exceptions')
    ->classes()
    ->toImplement(StandardsException::class);

arch('testing helpers stay out of production code')
    ->expect('Svnjn\Standards\Testing')
    ->toOnlyBeUsedIn('Svnjn\Standards\Testing');
