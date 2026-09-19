<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Svnjn\Standards\PHPStan\Rules\JsonThrowOnErrorRule;

/**
 * @extends RuleTestCase<JsonThrowOnErrorRule>
 */
final class JsonThrowOnErrorRuleTest extends RuleTestCase
{
    private const string TIP = 'Without it, invalid JSON silently returns null or false.';

    public function testReportsJsonCallsWithoutThrowOnError(): void
    {
        $this->analyse([__DIR__ . '/data/json.php'], [
            ['json_decode() must be called with JSON_THROW_ON_ERROR.', 7, self::TIP],
            ['json_decode() must be called with JSON_THROW_ON_ERROR.', 8, self::TIP],
            ['json_decode() must be called with JSON_THROW_ON_ERROR.', 11, self::TIP],
            ['json_encode() must be called with JSON_THROW_ON_ERROR.', 13, self::TIP],
            ['json_encode() must be called with JSON_THROW_ON_ERROR.', 15, self::TIP],
            ['json_encode() must be called with JSON_THROW_ON_ERROR.', 20, self::TIP],
            ['json_encode() must be called with JSON_THROW_ON_ERROR.', 30, self::TIP],
        ]);
    }

    protected function getRule(): Rule
    {
        return new JsonThrowOnErrorRule(self::getContainer()->getByType(ReflectionProvider::class));
    }
}
