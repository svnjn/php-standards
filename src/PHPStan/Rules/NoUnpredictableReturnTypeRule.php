<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use Svnjn\Standards\PHPStan\ExposedTypeInspector;
use Svnjn\Standards\PHPStan\ExposedTypeProblem;

/**
 * Public and protected methods must not return key-value arrays or mixed.
 *
 * Methods whose signature is dictated by a parent class or interface
 * (e.g. JsonSerializable::jsonSerialize()) are exempt.
 *
 * @implements Rule<InClassMethodNode>
 */
final readonly class NoUnpredictableReturnTypeRule implements Rule
{
    public function __construct(
        private ExposedTypeInspector $inspector,
    ) {}

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $method = $node->getMethodReflection();
        $class = $node->getClassReflection();

        if ($method->isPrivate() || $this->overridesInheritedMethod($class, $method->getName())) {
            return [];
        }

        $returnType = $method->getOnlyVariant()->getReturnType();
        $problem = $this->inspector->inspect($returnType);

        if ($problem === null) {
            return [];
        }

        $subject = sprintf('Method %s::%s()', $class->getDisplayName(), $method->getName());
        $type = $returnType->describe(VerbosityLevel::precise());

        return [
            match ($problem) {
                ExposedTypeProblem::KeyValueArray => RuleErrorBuilder::message(sprintf('%s returns %s, a key-value array.', $subject, $type))
                    ->identifier('svnjn.arrayReturn')
                    ->tip('Return a data object, or list<T> for a sequence. See docs/guidelines/data-objects.md in svnjn/php-standards.')
                    ->build(),
                ExposedTypeProblem::Mixed => RuleErrorBuilder::message(sprintf('%s returns mixed.', $subject))
                    ->identifier('svnjn.mixedReturn')
                    ->tip('Declare the precise type the method returns.')
                    ->build(),
            },
        ];
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
