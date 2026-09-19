<?php

declare(strict_types=1);

namespace Svnjn\Standards\Docs;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Svnjn\Standards\Exceptions\CommandFailedException;
use Svnjn\Standards\Exceptions\InvalidArgumentException;
use Svnjn\Standards\Exceptions\InvalidDataException;
use Svnjn\Standards\Internal\ArrayReader;
use Svnjn\Standards\Process\ProcessRunner;

/**
 * Checks every PHP example in the docs: a syntax check with `php -l`, then
 * PHPStan at level 5 so renamed classes, methods and arguments are caught.
 *
 * Undefined variables are allowed, so an example can use `$client` without
 * building it first. Give it a `@var Client $client` docblock to have the
 * calls on it checked too.
 */
final readonly class DocsChecker
{
    public const string WORK_DIRECTORY = 'build/docs-check';

    private const int PHPSTAN_LEVEL = 5;

    public function __construct(
        private ProcessRunner $processes,
        private string $projectRoot,
        private CodeBlockExtractor $extractor = new CodeBlockExtractor(),
    ) {}

    /**
     * @param  list<string>  $paths  Markdown files or directories, relative to the project root.
     */
    public function check(array $paths): DocsReport
    {
        $blocks = [];

        foreach ($this->markdownFiles($paths) as $file) {
            $relative = substr($file, strlen($this->projectRoot) + 1);
            $blocks = [...$blocks, ...$this->extractor->extract($relative, (string) file_get_contents($file))];
        }

        if ($blocks === []) {
            return new DocsReport(0, []);
        }

        $snippets = $this->writeSnippets($blocks);
        $problems = $this->lint($snippets);

        return new DocsReport(count($blocks), $problems !== [] ? $problems : $this->analyse($snippets));
    }

    /**
     * @param  list<string>  $paths
     * @return list<string>
     */
    private function markdownFiles(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            $absolute = $this->projectRoot . '/' . ltrim($path, '/');

            if (is_file($absolute)) {
                $files[] = $absolute;
            } elseif (is_dir($absolute)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolute, FilesystemIterator::SKIP_DOTS));

                foreach ($iterator as $file) {
                    if ($file instanceof SplFileInfo && $file->getExtension() === 'md') {
                        $files[] = $file->getPathname();
                    }
                }
            }
        }

        sort($files);

        return array_values(array_unique($files));
    }

    /**
     * @param  list<CodeBlock>  $blocks
     * @return array<string, CodeBlock> Snippet file => the example it came from.
     */
    private function writeSnippets(array $blocks): array
    {
        $directory = $this->emptyWorkDirectory();

        $snippets = [];

        foreach ($blocks as $index => $block) {
            $file = sprintf('%s/example-%03d.php', $directory, $index + 1);
            file_put_contents($file, $this->addOpeningTag($block->code));
            $snippets[$file] = $block;
        }

        return $snippets;
    }

    /**
     * @param  array<string, CodeBlock>  $snippets
     * @return list<DocsProblem>
     */
    private function lint(array $snippets): array
    {
        $problems = [];

        foreach ($snippets as $file => $block) {
            // Fixed ini settings: where php -l reports errors otherwise depends on the machine's php.ini.
            $result = $this->processes->run([PHP_BINARY, '-d', 'display_errors=stderr', '-d', 'log_errors=0', '-l', $file], $this->projectRoot);

            if ($result->successful()) {
                continue;
            }

            $message = $this->syntaxError($result->output . "\n" . $result->errorOutput);
            $line = preg_match('/on line (\d+)/', $message, $matches) === 1 ? (int) $matches[1] : 1;
            $message = (string) preg_replace('/^PHP\s+|\s+in \S+ on line \d+$/', '', $message);

            $problems[] = new DocsProblem($block->file, $this->markdownLine($block, $line), $message);
        }

        return $problems;
    }

    /**
     * @param  array<string, CodeBlock>  $snippets
     * @return list<DocsProblem>
     */
    private function analyse(array $snippets): array
    {
        $directory = $this->workDirectory();
        $config = $directory . '/phpstan.neon';

        file_put_contents($config, sprintf(
            "parameters:\n    level: %d\n    paths:\n        - %s\n    ignoreErrors:\n        -\n            identifier: variable.undefined\n            reportUnmatched: false\n",
            self::PHPSTAN_LEVEL,
            $directory,
        ));

        $command = [PHP_BINARY, $this->projectRoot . '/vendor/bin/phpstan', 'analyse', '--no-progress', '--no-interaction', '--error-format=json', '--memory-limit=1G', '--configuration=' . $config];
        $result = $this->processes->run($command, $this->projectRoot);

        if (! in_array($result->exitCode, [0, 1], true)) {
            throw CommandFailedException::for($command, $result->exitCode, $result->errorOutput);
        }

        try {
            $report = ArrayReader::fromJson($result->output);
        } catch (InvalidDataException $exception) {
            throw CommandFailedException::for($command, $result->exitCode, $exception->getMessage() . "\n" . $result->errorOutput);
        }

        $problems = [];
        $files = $report->object('files');

        foreach ($files->keys() as $file) {
            $block = $snippets[$file] ?? null;

            if ($block === null) {
                continue;
            }

            foreach ($files->object($file)->list('messages') as $message) {
                $problems[] = new DocsProblem($block->file, $this->markdownLine($block, $message->optionalInt('line') ?? 1), $message->string('message'));
            }
        }

        foreach ($report->strings('errors') as $error) {
            $problems[] = new DocsProblem('phpstan', 0, $error);
        }

        return $problems;
    }

    /**
     * The line of php -l's output that describes the error ("Parse error: ..."),
     * not its "Errors parsing <file>" summary.
     */
    private function syntaxError(string $output): string
    {
        $lines = array_values(array_filter(array_map(trim(...), explode("\n", $output)), static fn(string $line): bool => $line !== ''));

        foreach ($lines as $line) {
            if (preg_match('/(Parse|Fatal) error:/i', $line) === 1) {
                return $line;
            }
        }

        return $lines[0] ?? 'Syntax error.';
    }

    private function addOpeningTag(string $code): string
    {
        return str_starts_with(ltrim($code), '<?php') ? $code : "<?php\n" . $code;
    }

    private function markdownLine(CodeBlock $block, int $snippetLine): int
    {
        $addedLines = str_starts_with(ltrim($block->code), '<?php') ? 0 : 1;

        return $block->line + max(0, $snippetLine - 1 - $addedLines);
    }

    /**
     * The only directory the checker writes to or deletes from. Mutation testing
     * runs altered copies of this class for real, so any other path throws.
     */
    private function workDirectory(): string
    {
        $directory = $this->projectRoot . '/' . self::WORK_DIRECTORY;

        if (! str_ends_with($directory, '/' . self::WORK_DIRECTORY)) {
            throw new InvalidArgumentException(sprintf('Refusing to use "%s": the docs checker only writes to %s.', $directory, self::WORK_DIRECTORY));
        }

        return $directory;
    }

    private function emptyWorkDirectory(): string
    {
        $directory = $this->workDirectory();

        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);

            return $directory;
        }

        $contents = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($contents as $item) {
            if ($item instanceof SplFileInfo) {
                $item->isDir() && ! $item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }
        }

        return $directory;
    }
}
