<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Variable>
 */
final class NoGlobalsVariableRule implements Rule
{
    public function getNodeType(): string
    {
        return Variable::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name !== 'GLOBALS') {
            return [];
        }

        return [
            RuleErrorBuilder::message('$GLOBALS creates hidden shared state.')
                ->identifier('svnjn.globalsVariable')
                ->tip('Pass the value in as a constructor or method argument.')
                ->build(),
        ];
    }
}
