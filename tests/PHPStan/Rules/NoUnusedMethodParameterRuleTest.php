<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Svnjn\Standards\PHPStan\Rules\NoUnusedMethodParameterRule;
use Svnjn\Standards\PHPStan\UnusedParameterFinder;
use Svnjn\Standards\Tests\PHPStan\Rules\Data\UnusedMethodParameters\Examples;
use Svnjn\Standards\Tests\PHPStan\Rules\Data\UnusedMethodParameters\Status;

/**
 * @extends RuleTestCase<NoUnusedMethodParameterRule>
 */
final class NoUnusedMethodParameterRuleTest extends RuleTestCase
{
    public function testReportsUnusedParameters(): void
    {
        $class = Examples::class;
        $status = Status::class;
        $tip = NoUnusedMethodParameterRule::TIP;

        $this->analyse([__DIR__ . '/data/unused-method-parameters.php'], [
            ["Method {$class}::publicUnused() has an unused parameter \$unused.", 23, $tip],
            ["Method {$class}::protectedUnused() has an unused parameter \$unused.", 25, $tip],
            ["Method {$class}::privateUnused() has an unused parameter \$unused.", 27, $tip],
            ["Method {$class}::multiLine() has an unused parameter \$unused.", 31, $tip],
            ["Method {$class}::shadowedByClosure() has an unused parameter \$value.", 48, $tip],
            ["Method {$class}::shadowedByArrowFunctionParameter() has an unused parameter \$value.", 50, $tip],
            ["Method {$class}::shadowedByAnonymousClass() has an unused parameter \$value.", 52, $tip],
            ["Method {$class}::shadowedByNestedFunction() has an unused parameter \$value.", 66, $tip],
            ["Method {$class}::compactsSomethingElse() has an unused parameter \$value.", 84, $tip],
            ["Method {$class}::returnsConstant() has an unused parameter \$unused.", 99, $tip],
            ["Method {$status}::label() has an unused parameter \$locale.", 106, $tip],
        ]);
    }

    protected function getRule(): Rule
    {
        return new NoUnusedMethodParameterRule(new UnusedParameterFinder());
    }
}
