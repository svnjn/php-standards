<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Svnjn\Standards\PHPStan\Rules\NoGlobalsVariableRule;

/**
 * @extends RuleTestCase<NoGlobalsVariableRule>
 */
final class NoGlobalsVariableRuleTest extends RuleTestCase
{
    public function testReportsGlobalsVariable(): void
    {
        $this->analyse([__DIR__ . '/data/globals.php'], [
            ['$GLOBALS creates hidden shared state.', 14, 'Pass the value in as a constructor or method argument.'],
        ]);
    }

    protected function getRule(): Rule
    {
        return new NoGlobalsVariableRule();
    }
}
