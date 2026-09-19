<?php

declare(strict_types=1);

namespace Svnjn\Standards\Link;

use Svnjn\Standards\Console\Output;
use Svnjn\Standards\Exceptions\CommandFailedException;
use Svnjn\Standards\Exceptions\InvalidArgumentException;
use Svnjn\Standards\Internal\ArrayReader;
use Svnjn\Standards\Process\ProcessRunner;

/**
 * Links a local package into a local app through a Composer path repository,
 * so the app uses the package's working copy (symlinked, changes are live).
 */
final readonly class Linker
{
    public function __construct(
        private ProcessRunner $processes,
        private Output $output,
        private string $composer = 'composer',
    ) {}

    public function link(string $packagePath, string $appPath): void
    {
        $packagePath = $this->resolveProject($packagePath, 'package');
        $appPath = $this->resolveProject($appPath, 'app');
        $package = $this->packageName($packagePath);
        $state = LinkState::load($packagePath);

        if ($state->find($appPath) instanceof LinkedApp) {
            $this->output->line(sprintf('%s is already linked into %s.', $package, $appPath));

            return;
        }

        [$constraint, $dev] = $this->currentRequirement($appPath, $package);

        $this->composer($appPath, ['config', 'repositories.' . $this->repositoryKey($package), json_encode(
            ['type' => 'path', 'url' => $packagePath, 'options' => ['symlink' => true]],
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        )]);
        $this->composer($appPath, ['require', $package . ':*@dev', '--with-dependencies', ...($dev ? ['--dev'] : [])]);

        $state->with(new LinkedApp($appPath, $constraint, $dev))->save($packagePath);

        $this->output->line(sprintf('Linked %s into %s. Don\'t commit the app\'s composer.json or composer.lock while linked.', $package, $appPath));
    }

    public function unlink(string $packagePath, string $appPath): void
    {
        $packagePath = $this->resolveProject($packagePath, 'package');
        $appPath = $this->resolveProject($appPath, 'app');
        $package = $this->packageName($packagePath);
        $state = LinkState::load($packagePath);
        $app = $state->find($appPath);

        if (! $app instanceof LinkedApp) {
            $this->output->line(sprintf('%s is not linked into %s.', $package, $appPath));

            return;
        }

        $this->composer($appPath, ['config', '--unset', 'repositories.' . $this->repositoryKey($package)]);

        if ($app->originalConstraint === null) {
            $this->composer($appPath, ['remove', $package, ...($app->dev ? ['--dev'] : [])]);
        } else {
            $this->composer($appPath, ['require', $package . ':' . $app->originalConstraint, '--with-dependencies', ...($app->dev ? ['--dev'] : [])]);
        }

        $state->without($appPath)->save($packagePath);

        $this->output->line(sprintf('Unlinked %s from %s.', $package, $appPath));
    }

    private function resolveProject(string $path, string $kind): string
    {
        $resolved = realpath($path);

        if ($resolved === false || ! is_file($resolved . '/composer.json')) {
            throw new InvalidArgumentException(sprintf('No composer.json found in the %s directory "%s".', $kind, $path));
        }

        return $resolved;
    }

    private function packageName(string $packagePath): string
    {
        return $this->composerJson($packagePath)->string('name');
    }

    /**
     * @return array{?string, bool} The constraint the app required the package with, and whether it was in require-dev.
     */
    private function currentRequirement(string $appPath, string $package): array
    {
        $composer = $this->composerJson($appPath);

        foreach (['require' => false, 'require-dev' => true] as $section => $dev) {
            $constraint = $composer->optionalObject($section)?->optionalString($package);

            if ($constraint !== null) {
                return [$constraint, $dev];
            }
        }

        return [null, false];
    }

    private function composerJson(string $path): ArrayReader
    {
        return ArrayReader::fromJson((string) file_get_contents($path . '/composer.json'));
    }

    private function repositoryKey(string $package): string
    {
        return 'svnjn-link-' . str_replace('/', '-', $package);
    }

    /**
     * @param  list<string>  $arguments
     */
    private function composer(string $appPath, array $arguments): void
    {
        $command = [$this->composer, ...$arguments];
        $exitCode = $this->processes->stream($command, $appPath);

        if ($exitCode !== 0) {
            throw CommandFailedException::for($command, $exitCode, '');
        }
    }
}
