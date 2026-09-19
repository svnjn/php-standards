<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * json_decode() and json_encode() must pass JSON_THROW_ON_ERROR, so invalid
 * input throws instead of silently returning null or false.
 *
 * @implements Rule<FuncCall>
 */
final readonly class JsonThrowOnErrorRule implements Rule
{
    /** Zero-based position of the $flags argument. */
    private const array FLAGS_POSITION = [
        'json_decode' => 3,
        'json_encode' => 1,
    ];

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {}

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Name) {
            return [];
        }

        $resolved = $this->reflectionProvider->resolveFunctionName($node->name, $scope);
        $function = $resolved === null ? null : strtolower($resolved);

        if ($function === null || ! array_key_exists($function, self::FLAGS_POSITION)) {
            return [];
        }

        $flags = $this->findFlagsArgument($node, self::FLAGS_POSITION[$function]);

        if (! $flags instanceof Arg) {
            return [$this->error($function)];
        }

        $values = $scope->getType($flags->value)->getConstantScalarValues();

        if ($values === []) {
            return [$this->error($function)];
        }

        foreach ($values as $value) {
            if (! is_int($value) || ($value & JSON_THROW_ON_ERROR) === 0) {
                return [$this->error($function)];
            }
        }

        return [];
    }

    private function findFlagsArgument(FuncCall $call, int $position): ?Arg
    {
        foreach ($call->getArgs() as $index => $arg) {
            if ($arg->unpack) {
                return null;
            }

            if ($arg->name?->toString() === 'flags' || ($arg->name === null && $index === $position)) {
                return $arg;
            }
        }

        return null;
    }

    private function error(string $function): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf('%s() must be called with JSON_THROW_ON_ERROR.', $function))
            ->identifier('svnjn.jsonThrowOnError')
            ->tip('Without it, invalid JSON silently returns null or false.')
            ->build();
    }
}
