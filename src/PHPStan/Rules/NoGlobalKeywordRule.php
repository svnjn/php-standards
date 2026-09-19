<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Global_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Global_>
 */
final class NoGlobalKeywordRule implements Rule
{
    public function getNodeType(): string
    {
        return Global_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return [
            RuleErrorBuilder::message('The global keyword creates hidden shared state.')
                ->identifier('svnjn.globalKeyword')
                ->tip('Pass the value in as a constructor or method argument.')
                ->build(),
        ];
    }
}
