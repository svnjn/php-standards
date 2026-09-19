<?php

declare(strict_types=1);

namespace Svnjn\Standards\Exceptions;

use UnexpectedValueException;

final class InvalidDataException extends UnexpectedValueException implements StandardsException {}
