<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use Svnjn\Standards\PHPStan\ExposedTypeInspector;
use Svnjn\Standards\PHPStan\ExposedTypeProblem;

/**
 * Public properties (the fields of data objects) must not be key-value arrays or mixed.
 *
 * Properties redeclared from a parent class are exempt.
 *
 * @implements Rule<ClassPropertyNode>
 */
final readonly class NoUnpredictablePropertyTypeRule implements Rule
{
    public function __construct(
        private ExposedTypeInspector $inspector,
    ) {}

    public function getNodeType(): string
    {
        return ClassPropertyNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();

        if (! $node->isPublic() || $this->redeclaresInheritedProperty($class, $node->getName())) {
            return [];
        }

        $propertyType = $class->getNativeProperty($node->getName())->getReadableType();
        $problem = $this->inspector->inspect($propertyType);

        if ($problem === null) {
            return [];
        }

        $subject = sprintf('Property %s::$%s', $class->getDisplayName(), $node->getName());
        $type = $propertyType->describe(VerbosityLevel::precise());

        return [
            match ($problem) {
                ExposedTypeProblem::KeyValueArray => RuleErrorBuilder::message(sprintf('%s is %s, a key-value array.', $subject, $type))
                    ->identifier('svnjn.arrayProperty')
                    ->tip('Use a data object, or list<T> for a sequence. See docs/guidelines/data-objects.md in svnjn/php-standards.')
                    ->build(),
                ExposedTypeProblem::Mixed => RuleErrorBuilder::message(sprintf('%s is mixed.', $subject))
                    ->identifier('svnjn.mixedProperty')
                    ->tip('Declare the precise type of the property.')
                    ->build(),
            },
        ];
    }

    private function redeclaresInheritedProperty(ClassReflection $class, string $propertyName): bool
    {
        foreach ($class->getParents() as $parent) {
            if ($parent->hasNativeProperty($propertyName)) {
                return true;
            }
        }

        return false;
    }
}
