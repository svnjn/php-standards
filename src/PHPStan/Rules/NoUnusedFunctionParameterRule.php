<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InFunctionNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Svnjn\Standards\PHPStan\UnusedParameterFinder;

/**
 * Every parameter of a function must be used. Closures are exempt: whoever
 * calls them decides their parameters.
 *
 * @implements Rule<InFunctionNode>
 */
final readonly class NoUnusedFunctionParameterRule implements Rule
{
    public function __construct(
        private UnusedParameterFinder $finder,
    ) {}

    public function getNodeType(): string
    {
        return InFunctionNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $function = $node->getFunctionReflection()->getName();
        $errors = [];

        foreach ($this->finder->find($node->getOriginalNode()) as $parameter) {
            $errors[] = RuleErrorBuilder::message(sprintf('Function %s() has an unused parameter $%s.', $function, $parameter->name))
                ->identifier('svnjn.unusedParameter')
                ->line($parameter->line)
                ->tip(NoUnusedMethodParameterRule::TIP)
                ->build();
        }

        return $errors;
    }
}
