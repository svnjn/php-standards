<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Support;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * A scratch directory for one test. tests/Pest.php deletes every directory
 * created during a test after it finishes, pass or fail.
 */
final class TemporaryDirectory
{
    /** @var list<string> */
    private static array $created = [];

    private function __construct(
        public readonly string $path,
    ) {}

    public static function create(): self
    {
        $path = sys_get_temp_dir() . '/svnjn-standards-' . bin2hex(random_bytes(6));
        mkdir($path, 0o755, true);
        $path = (string) realpath($path);
        self::$created[] = $path;

        return new self($path);
    }

    public static function cleanUp(): void
    {
        foreach (self::$created as $path) {
            if (is_dir($path)) {
                self::remove($path);
            }
        }

        self::$created = [];
    }

    public function write(string $relativePath, string $contents): string
    {
        $file = $this->path . '/' . $relativePath;

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0o755, true);
        }

        file_put_contents($file, $contents);

        return $file;
    }

    private static function remove(string $path): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if ($item instanceof SplFileInfo) {
                $item->isDir() && ! $item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }
        }

        rmdir($path);
    }
}
