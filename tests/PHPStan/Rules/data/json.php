<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules\Data\Json;

json_decode('{}');
json_decode('{}', true);
json_decode('{}', true, 512, JSON_THROW_ON_ERROR);
json_decode('{}', flags: JSON_THROW_ON_ERROR);
json_decode('{}', true, 512, JSON_BIGINT_AS_STRING);
json_decode('{}', true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
json_encode([]);
json_encode([], JSON_THROW_ON_ERROR);
json_encode([], JSON_PRETTY_PRINT);
\json_encode([], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

function dynamicFlags(int $flags): void
{
    json_encode([], $flags);
}

function dynamicName(string $function): void
{
    $function('{}');
}

function spreadArguments(array $arguments): void
{
    json_encode(...$arguments);
}

function unknownFunction(): void
{
    not_a_real_function('{}');
}
