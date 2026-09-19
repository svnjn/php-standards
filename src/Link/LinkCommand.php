<?php

declare(strict_types=1);

namespace Svnjn\Standards\Link;

use Svnjn\Standards\Console\Output;
use Svnjn\Standards\Exceptions\StandardsException;

/**
 * vendor/bin/svnjn-link <app-path>     link this package into a local app
 * vendor/bin/svnjn-unlink <app-path>   put the app back on the released version
 */
final readonly class LinkCommand
{
    public function __construct(
        private Linker $linker,
        private Output $output,
        private string $packagePath,
    ) {}

    /**
     * @param  list<string>  $arguments
     */
    public function link(array $arguments): int
    {
        return $this->handle($arguments, 'svnjn-link', fn(string $app) => $this->linker->link($this->packagePath, $app));
    }

    /**
     * @param  list<string>  $arguments
     */
    public function unlink(array $arguments): int
    {
        return $this->handle($arguments, 'svnjn-unlink', fn(string $app) => $this->linker->unlink($this->packagePath, $app));
    }

    /**
     * @param  list<string>  $arguments
     * @param  callable(string): void  $action
     */
    private function handle(array $arguments, string $name, callable $action): int
    {
        if (count($arguments) !== 1) {
            $this->output->error(sprintf('Usage: vendor/bin/%s <path-to-app>', $name));

            return 1;
        }

        try {
            $action($arguments[0]);
        } catch (StandardsException $exception) {
            $this->output->error($exception->getMessage());

            return 1;
        }

        return 0;
    }
}
