<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Svnjn\Standards\PHPStan\Rules\NoErrorSuppressionRule;

/**
 * @extends RuleTestCase<NoErrorSuppressionRule>
 */
final class NoErrorSuppressionRuleTest extends RuleTestCase
{
    public function testReportsErrorSuppression(): void
    {
        $this->analyse([__DIR__ . '/data/error-suppression.php'], [
            ['Error suppression (@) hides failures.', 5, 'Check the condition first, or let the error surface and handle the exception.'],
        ]);
    }

    protected function getRule(): Rule
    {
        return new NoErrorSuppressionRule();
    }
}
