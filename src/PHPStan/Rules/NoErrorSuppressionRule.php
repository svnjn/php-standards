<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ErrorSuppress;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<ErrorSuppress>
 */
final class NoErrorSuppressionRule implements Rule
{
    public function getNodeType(): string
    {
        return ErrorSuppress::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return [
            RuleErrorBuilder::message('Error suppression (@) hides failures.')
                ->identifier('svnjn.errorSuppression')
                ->tip('Check the condition first, or let the error surface and handle the exception.')
                ->build(),
        ];
    }
}
