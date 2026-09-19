<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules\Data\MagicMethods;

final class Magic
{
    public function __construct() {}

    public function __get(string $name): int { return 1; }

    public function __set(string $name, int $value): void {}

    public function __call(string $name, array $arguments): int { return 1; }

    public static function __callStatic(string $name, array $arguments): int { return 1; }

    public function __toString(): string { return ''; }
}
