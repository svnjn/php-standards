<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Svnjn\Standards\PHPStan\ExposedTypeInspector;
use Svnjn\Standards\PHPStan\Rules\NoUnpredictableReturnTypeRule;
use Svnjn\Standards\Tests\PHPStan\Rules\Data\ReturnTypes\Examples;

/**
 * @extends RuleTestCase<NoUnpredictableReturnTypeRule>
 */
final class NoUnpredictableReturnTypeRuleTest extends RuleTestCase
{
    private const string ARRAY_TIP = 'Return a data object, or list<T> for a sequence. See docs/guidelines/data-objects.md in svnjn/php-standards.';

    private const string MIXED_TIP = 'Declare the precise type the method returns.';

    public function testReportsKeyValueArraysAndMixed(): void
    {
        $class = Examples::class;

        $this->analyse([__DIR__ . '/data/return-types.php'], [
            ["Method {$class}::map() returns array<string, int>, a key-value array.", 19, self::ARRAY_TIP],
            ["Method {$class}::shape() returns array{id: int}, a key-value array.", 22, self::ARRAY_TIP],
            ["Method {$class}::plainArray() returns array, a key-value array.", 24, self::ARRAY_TIP],
            ["Method {$class}::anything() returns mixed.", 26, self::MIXED_TIP],
            ["Method {$class}::nested() returns list<array<string, int>>, a key-value array.", 32, self::ARRAY_TIP],
            ["Method {$class}::tuple() returns array{int, string}, a key-value array.", 38, self::ARRAY_TIP],
            ["Method {$class}::iterable() returns iterable<string, Svnjn\Standards\Tests\PHPStan\Rules\Data\ReturnTypes\Invoice>, a key-value array.", 41, self::ARRAY_TIP],
            ["Method {$class}::protectedMixed() returns mixed.", 43, self::MIXED_TIP],
        ]);
    }

    protected function getRule(): Rule
    {
        return new NoUnpredictableReturnTypeRule(new ExposedTypeInspector());
    }
}
