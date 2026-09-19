<?php

declare(strict_types=1);

namespace Svnjn\Standards\Rector;

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\Configuration\RectorConfigBuilder;
use Svnjn\Standards\Internal\ArrayReader;

/**
 * Shared Rector configuration. A package's rector.php only adds its paths:
 *
 *     return SvnjnRector::configure()
 *         ->withPaths([__DIR__ . '/src', __DIR__ . '/tests']);
 */
final class SvnjnRector
{
    /** The newest PHP a package can require; nothing newer exists to downgrade from. */
    private const string NEWEST_PHP = '8.5';

    public static function configure(?string $composerJson = null): RectorConfigBuilder
    {
        $builder = RectorConfig::configure()
            // Upgrades up to the lowest PHP version in composer.json, never beyond.
            ->withPhpSets()
            ->withPreparedSets(
                deadCode: true,
                codeQuality: true,
                typeDeclarations: true,
                privatization: true,
                instanceOf: true,
                earlyReturn: true,
            )
            ->withTreatClassesAsFinal()
            ->withSkip([
                // `=== null` reads better than `! $x instanceof Foo`.
                FlipTypeControlToUseExclusiveTypeRector::class,
            ]);

        // Rewrites syntax newer than the lowest supported PHP (e.g. PHP 8.4's
        // `new Foo()->bar()` in a package that supports 8.3). PHPStan parses with
        // the PHP it runs on, so without this such code would pass locally and
        // fail on the oldest PHP.
        $lowest = self::lowestPhpVersion($composerJson ?? getcwd() . '/composer.json');

        if ($lowest !== null && version_compare($lowest, self::NEWEST_PHP, '<')) {
            $builder->withDowngradeSets(...['php' . str_replace('.', '', $lowest) => true]);
        }

        return $builder;
    }

    /**
     * The lowest PHP minor version the package's composer.json allows, e.g. "8.3" for "^8.3 || ^9.0".
     */
    public static function lowestPhpVersion(string $composerJson): ?string
    {
        if (! is_file($composerJson)) {
            return null;
        }

        $constraint = ArrayReader::fromJson((string) file_get_contents($composerJson))
            ->optionalObject('require')
            ?->optionalString('php');

        if ($constraint === null || preg_match_all('/(\d+)\.(\d+)/', $constraint, $matches, PREG_SET_ORDER) === 0) {
            return null;
        }

        $lowest = null;

        foreach ($matches as $match) {
            $version = $match[1] . '.' . $match[2];

            if ($lowest === null || version_compare($version, $lowest, '<')) {
                $lowest = $version;
            }
        }

        return $lowest;
    }
}
