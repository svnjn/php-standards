<?php

declare(strict_types=1);

namespace Svnjn\Standards\Link;

use Svnjn\Standards\Internal\ArrayReader;

/**
 * The apps a package is linked into, stored in the package's .svnjn/links.json.
 */
final readonly class LinkState
{
    public const string FILE = '.svnjn/links.json';

    /**
     * @param  list<LinkedApp>  $apps
     */
    public function __construct(
        public array $apps = [],
    ) {}

    public static function load(string $packagePath): self
    {
        $file = $packagePath . '/' . self::FILE;

        if (! is_file($file)) {
            return new self();
        }

        return new self(array_map(
            LinkedApp::fromReader(...),
            ArrayReader::fromJson((string) file_get_contents($file))->list('apps'),
        ));
    }

    public function find(string $appPath): ?LinkedApp
    {
        foreach ($this->apps as $app) {
            if ($app->path === $appPath) {
                return $app;
            }
        }

        return null;
    }

    public function with(LinkedApp $app): self
    {
        return new self([...$this->without($app->path)->apps, $app]);
    }

    public function without(string $appPath): self
    {
        return new self(array_values(array_filter(
            $this->apps,
            static fn(LinkedApp $app): bool => $app->path !== $appPath,
        )));
    }

    public function save(string $packagePath): void
    {
        $file = $packagePath . '/' . self::FILE;

        if ($this->apps === []) {
            if (is_file($file)) {
                unlink($file);
            }

            if (is_dir(dirname($file)) && scandir(dirname($file)) === ['.', '..']) {
                rmdir(dirname($file));
            }

            return;
        }

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0o755, true);
        }

        file_put_contents($file, json_encode(
            ['apps' => $this->apps],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ) . "\n");
    }
}
