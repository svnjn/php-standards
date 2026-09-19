<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan;

use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Decides whether a type exposed by a public API is predictable.
 *
 * Objects, enums, scalars and list<T> of those are predictable.
 * Key-value arrays (array<K, V>, shapes, tuples) and explicit mixed are not.
 */
final class ExposedTypeInspector
{
    private const int MAX_DEPTH = 5;

    public function inspect(Type $type): ?ExposedTypeProblem
    {
        return $this->inspectAtDepth($type, 0);
    }

    private function inspectAtDepth(Type $type, int $depth): ?ExposedTypeProblem
    {
        if ($depth > self::MAX_DEPTH) {
            return null;
        }

        $type = TypeCombinator::removeNull($type);

        if ($type instanceof MixedType) {
            // Implicit mixed (a missing type) is already reported by PHPStan itself.
            return $type->isExplicitMixed() ? ExposedTypeProblem::Mixed : null;
        }

        if ($type->isArray()->no()) {
            return null;
        }

        if (! $type->isList()->yes() || $type->isConstantArray()->yes()) {
            return ExposedTypeProblem::KeyValueArray;
        }

        return $this->inspectAtDepth($type->getIterableValueType(), $depth + 1);
    }
}
