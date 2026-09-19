<?php

declare(strict_types=1);

namespace Svnjn\Standards\Pest;

use Pest\Arch\Contracts\ArchExpectation;

/**
 * The `svnjn` arch preset: bans what Pest's php, security and strict presets miss.
 *
 * Register it once in tests/Pest.php, then use it in an arch test:
 *
 *     SvnjnPreset::register();
 *
 *     arch()->preset()->svnjn();
 */
final class SvnjnPreset
{
    public const string NAME = 'svnjn';

    /** Debug helpers and PHP features that hide values from analysis. */
    public const array BANNED_FUNCTIONS = ['dd', 'compact', 'exit'];

    /**
     * Mutable Carbon: use CarbonImmutable, DateTimeImmutable or a PSR-20 clock.
     *
     * PHP's own DateTime is invisible to arch tests; Pint's date_time_immutable
     * fixer rewrites it instead, so `composer lint` fails on it.
     */
    public const array BANNED_CLASSES = ['Carbon\Carbon'];

    public static function register(): void
    {
        pest()->presets()->custom(self::NAME, self::expectations(...));
    }

    /**
     * @param  array<int, string>  $namespaces
     * @return list<ArchExpectation>
     */
    public static function expectations(array $namespaces): array
    {
        $expectations = [];

        foreach ($namespaces as $namespace) {
            $expectations[] = expect($namespace)->not->toUse(self::BANNED_FUNCTIONS);
            $expectations[] = expect($namespace)->not->toUse(self::BANNED_CLASSES);
        }

        return $expectations;
    }
}
