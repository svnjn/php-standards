<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Svnjn\Standards\PHPStan\ExposedTypeInspector;
use Svnjn\Standards\PHPStan\Rules\NoUnpredictablePropertyTypeRule;

/**
 * @extends RuleTestCase<NoUnpredictablePropertyTypeRule>
 */
final class NoUnpredictablePropertyTypeRuleTest extends RuleTestCase
{
    private const string ARRAY_TIP = 'Use a data object, or list<T> for a sequence. See docs/guidelines/data-objects.md in svnjn/php-standards.';

    public function testReportsKeyValueArraysAndMixed(): void
    {
        $namespace = 'Svnjn\Standards\Tests\PHPStan\Rules\Data\PropertyTypes';

        $this->analyse([__DIR__ . '/data/property-types.php'], [
            ["Property {$namespace}\\Data::\$map is array<string, int>, a key-value array.", 17, self::ARRAY_TIP],
            ["Property {$namespace}\\Data::\$anything is mixed.", 19, 'Declare the precise type of the property.'],
            ["Property {$namespace}\\Base::\$items is array<string, int>, a key-value array.", 28, self::ARRAY_TIP],
        ]);
    }

    protected function getRule(): Rule
    {
        return new NoUnpredictablePropertyTypeRule(new ExposedTypeInspector());
    }
}
