<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Svnjn\Standards\PHPStan\UnusedParameterFinder;

/**
 * Every parameter of a method must be used.
 *
 * Methods whose signature is dictated by a parent class or interface are
 * exempt, and so are magic methods (__invoke(), __unserialize(), ...), whose
 * parameters PHP or the caller decides. PHPStan itself reports constructors.
 *
 * @implements Rule<InClassMethodNode>
 */
final readonly class NoUnusedMethodParameterRule implements Rule
{
    public const string TIP = 'Remove it. If a framework passes it anyway, see docs/guidelines/coding-standards.md in svnjn/php-standards.';

    public function __construct(
        private UnusedParameterFinder $finder,
    ) {}

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();
        $method = $node->getMethodReflection()->getName();

        if (str_starts_with($method, '__') || $this->overridesInheritedMethod($class, $method)) {
            return [];
        }

        $errors = [];

        foreach ($this->finder->find($node->getOriginalNode()) as $parameter) {
            $errors[] = RuleErrorBuilder::message(sprintf('Method %s::%s() has an unused parameter $%s.', $class->getDisplayName(), $method, $parameter->name))
                ->identifier('svnjn.unusedParameter')
                ->line($parameter->line)
                ->tip(self::TIP)
                ->build();
        }

        return $errors;
    }

    private function overridesInheritedMethod(ClassReflection $class, string $methodName): bool
    {
        foreach ([...$class->getParents(), ...$class->getInterfaces()] as $ancestor) {
            if ($ancestor->hasNativeMethod($methodName)) {
                return true;
            }
        }

        return false;
    }
}
