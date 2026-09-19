<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules\Data\UnusedFunctionParameters;

function unused(string $used, string $unused): string { return $used; }

function allUsed(string $first, string $second): string { return $first . $second; }

/** @return list<mixed> */
function readsAllArguments(string $first): array { return func_get_args(); }

$closure = function (string $decidedByCaller): void {};

$arrow = fn (string $decidedByCaller): int => 1;
