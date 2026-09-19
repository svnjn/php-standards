<?php

declare(strict_types=1);

namespace Svnjn\Standards\Exceptions;

use Throwable;

/**
 * Every exception thrown by this package implements this interface,
 * so callers can catch all of them with one catch block.
 */
interface StandardsException extends Throwable {}
