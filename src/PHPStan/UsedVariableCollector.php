<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

/**
 * Records which variables a function body mentions. A nested closure counts
 * only through its `use` list; nested functions and classes have their own scope.
 *
 * @internal
 */
final class UsedVariableCollector extends NodeVisitorAbstract
{
    /** Functions that read every variable in scope without naming it. */
    private const array READS_EVERY_VARIABLE = ['func_get_arg', 'func_get_args', 'get_defined_vars'];

    /** @var array<string, true> */
    private array $names = [];

    private bool $readsEveryVariable = false;

    public function uses(string $name): bool
    {
        return $this->readsEveryVariable || isset($this->names[$name]);
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Closure) {
            foreach ($node->uses as $use) {
                $this->record($use->var);
            }

            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof ClassLike || $node instanceof Function_ || $node instanceof Param) {
            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Variable) {
            $this->record($node);
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $this->recordCall($node, $node->name->toLowerString());
        }

        return null;
    }

    private function record(Variable $variable): void
    {
        if (is_string($variable->name)) {
            $this->names[$variable->name] = true;
        }
    }

    private function recordCall(FuncCall $call, string $function): void
    {
        if (in_array($function, self::READS_EVERY_VARIABLE, true)) {
            $this->readsEveryVariable = true;
        }

        if ($function !== 'compact') {
            return;
        }

        foreach ($call->getArgs() as $argument) {
            if ($argument->value instanceof String_) {
                $this->names[$argument->value->value] = true;
            }
        }
    }
}
