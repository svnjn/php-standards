<?php

declare(strict_types=1);

namespace Svnjn\Standards\PHPStan;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;

/**
 * Finds the parameters a function or method body never mentions.
 */
final class UnusedParameterFinder
{
    /**
     * @return list<UnusedParameter>
     */
    public function find(ClassMethod|Function_ $function): array
    {
        if ($function->stmts === null) {
            return [];
        }

        $collector = new UsedVariableCollector();
        (new NodeTraverser($collector))->traverse($function->stmts);

        $unused = [];

        foreach ($function->params as $parameter) {
            if ($parameter->var instanceof Variable && is_string($parameter->var->name) && ! $collector->uses($parameter->var->name)) {
                $unused[] = new UnusedParameter($parameter->var->name, $parameter->getStartLine());
            }
        }

        return $unused;
    }
}
