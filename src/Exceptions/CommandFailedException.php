<?php

declare(strict_types=1);

namespace Svnjn\Standards\Exceptions;

use RuntimeException;

final class CommandFailedException extends RuntimeException implements StandardsException
{
    /**
     * @param  list<string>  $command
     */
    public static function for(array $command, int $exitCode, string $errorOutput): self
    {
        return new self(sprintf(
            'Command "%s" failed with exit code %d.%s',
            implode(' ', $command),
            $exitCode,
            trim($errorOutput) === '' ? '' : "\n" . trim($errorOutput),
        ));
    }
}
