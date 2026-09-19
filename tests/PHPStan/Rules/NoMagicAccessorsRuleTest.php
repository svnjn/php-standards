<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Svnjn\Standards\PHPStan\Rules\NoMagicAccessorsRule;
use Svnjn\Standards\Tests\PHPStan\Rules\Data\MagicMethods\Magic;

/**
 * @extends RuleTestCase<NoMagicAccessorsRule>
 */
final class NoMagicAccessorsRuleTest extends RuleTestCase
{
    private const string TIP = 'Declare real properties or methods so PHPStan and your editor can see them.';

    public function testReportsMagicAccessors(): void
    {
        $class = Magic::class;

        $this->analyse([__DIR__ . '/data/magic-methods.php'], [
            ["Class {$class} declares magic method __get().", 11, self::TIP],
            ["Class {$class} declares magic method __set().", 13, self::TIP],
            ["Class {$class} declares magic method __call().", 15, self::TIP],
            ["Class {$class} declares magic method __callStatic().", 17, self::TIP],
        ]);
    }

    protected function getRule(): Rule
    {
        return new NoMagicAccessorsRule();
    }
}
