<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan;

enum ExposedTypeProblem
{
    /** array<K, V>, array shapes and tuples: callers can't know which keys exist. */
    case KeyValueArray;

    /** Explicit mixed: callers can't know anything about the value. */
    case Mixed;
}
