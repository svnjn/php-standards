<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Svnjn\Standards\PHPStan\Rules\NoUnusedFunctionParameterRule;
use Svnjn\Standards\PHPStan\Rules\NoUnusedMethodParameterRule;
use Svnjn\Standards\PHPStan\UnusedParameterFinder;

/**
 * @extends RuleTestCase<NoUnusedFunctionParameterRule>
 */
final class NoUnusedFunctionParameterRuleTest extends RuleTestCase
{
    public function testReportsUnusedParametersButNotClosures(): void
    {
        $this->analyse([__DIR__ . '/data/unused-function-parameters.php'], [
            [
                'Function Svnjn\Standards\Tests\PHPStan\Rules\Data\UnusedFunctionParameters\unused() has an unused parameter $unused.',
                7,
                NoUnusedMethodParameterRule::TIP,
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new NoUnusedFunctionParameterRule(new UnusedParameterFinder());
    }
}
