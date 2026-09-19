<?php

declare(strict_types=1);

function readGlobal(): mixed
{
    global $config;

    return $config;
}

function readGlobals(): mixed
{
    return $GLOBALS['config'];
}

function readLocal(): int
{
    $config = 1;

    return $config;
}
