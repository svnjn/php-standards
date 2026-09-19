<?php

declare(strict_types=1);

use Svnjn\Standards\Rector\SvnjnRector;

return SvnjnRector::configure()
    ->withCache(sys_get_temp_dir() . '/svnjn-standards-rector-fixture');
