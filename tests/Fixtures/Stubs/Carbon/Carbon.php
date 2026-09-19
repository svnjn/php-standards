<?php

declare(strict_types=1);

namespace Carbon;

/**
 * Stand-in for nesbot/carbon, so the arch preset test can see the class.
 */
final class Carbon
{
    public static function now(): self
    {
        return new self();
    }
}
