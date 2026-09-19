<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules\Data\UnusedMethodParameters;

interface Greeter
{
    public function greet(string $name, string $greeting): string;
}

abstract class Base
{
    public function describe(string $format): string { return $format; }

    abstract public function render(string $template): string;
}

final class Examples extends Base implements Greeter
{
    public function __construct(string $leftToPhpstan) {}

    public function publicUnused(string $used, string $unused): string { return $used; }

    protected function protectedUnused(int $unused): void {}

    private function privateUnused(int $unused): void {}

    public function multiLine(
        string $used,
        string $unused,
    ): string {
        return $used;
    }

    public function greet(string $name, string $greeting): string { return $name; }

    public function describe(string $format): string { return 'fixed'; }

    public function render(string $template): string { return 'fixed'; }

    public function __invoke(string $magic): void {}

    public function capturedByClosure(string $value): callable { return function () use ($value): string { return $value; }; }

    public function capturedByArrowFunction(string $value): callable { return fn (): string => $value; }

    public function shadowedByClosure(string $value): callable { return function (string $value): string { return $value; }; }

    public function shadowedByArrowFunctionParameter(string $value): callable { return fn (string $value): int => 1; }

    public function shadowedByAnonymousClass(string $value): object
    {
        return new class {
            public function value(string $value): string { return $value; }
        };
    }

    public function passedToAnonymousClass(string $value): object
    {
        return new class ($value) {
            public function __construct(public string $value) {}
        };
    }

    public function shadowedByNestedFunction(string $value): void
    {
        function nestedHelper(string $value): string { return $value; }
    }

    /** @return list<mixed> */
    public function readsAllArguments(string $first, string $second): array { return \func_get_args(); }

    public function readsOneArgument(string $first): mixed { return func_get_arg(0); }

    // PHP function names are case-insensitive.
    /** @return array<string, mixed> */
    public function readsDefinedVariables(string $value): array { return Get_Defined_Vars(); }

    /** @return array<string, mixed> */
    public function compacted(string $value): array { return compact('value'); }

    /** @return array<string, mixed> */
    public function compactsSomethingElse(string $value): array
    {
        $other = 'other';

        return compact('other');
    }

    public function variableVariable(string $name): mixed { return $$name; }

    public function writtenOnly(string $value): void { $value = 'overwritten'; }

    public function byReference(?string &$out): void { $out = 'written'; }

    public function callsCallable(callable $callback): mixed { return $callback(); }

    public function returnsConstant(string $unused): ?string { return null; }
}

enum Status: string
{
    case Paid = 'paid';

    public function label(string $locale): string { return 'Paid'; }
}

interface OnlySignatures
{
    public function withoutBody(string $unused): void;
}

abstract class AbstractWithoutBody
{
    abstract public function withoutBody(string $unused): void;
}
