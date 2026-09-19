<?php

declare(strict_types=1);

$contents = @file_get_contents('missing.txt');
$ok = file_get_contents('present.txt');
