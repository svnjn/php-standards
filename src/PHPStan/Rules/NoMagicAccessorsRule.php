<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Classes must not declare magic accessors: static analysis and autocomplete
 * can't see the members they fake.
 *
 * @implements Rule<InClassMethodNode>
 */
final class NoMagicAccessorsRule implements Rule
{
    private const array MAGIC_METHODS = ['__get', '__set', '__isset', '__unset', '__call', '__callstatic'];

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $method = $node->getMethodReflection()->getName();

        if (! in_array(strtolower($method), self::MAGIC_METHODS, true)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf('Class %s declares magic method %s().', $node->getClassReflection()->getDisplayName(), $method))
                ->identifier('svnjn.magicMethod')
                ->tip('Declare real properties or methods so PHPStan and your editor can see them.')
                ->build(),
        ];
    }
}
