<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Svnjn\Standards\PHPStan\Rules\NoGlobalKeywordRule;

/**
 * @extends RuleTestCase<NoGlobalKeywordRule>
 */
final class NoGlobalKeywordRuleTest extends RuleTestCase
{
    public function testReportsGlobalKeyword(): void
    {
        $this->analyse([__DIR__ . '/data/globals.php'], [
            ['The global keyword creates hidden shared state.', 7, 'Pass the value in as a constructor or method argument.'],
        ]);
    }

    protected function getRule(): Rule
    {
        return new NoGlobalKeywordRule();
    }
}
