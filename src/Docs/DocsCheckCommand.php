<?php

declare(strict_types=1);

namespace Svnjn\Standards\Docs;

use Svnjn\Standards\Console\Output;
use Svnjn\Standards\Exceptions\StandardsException;

/**
 * vendor/bin/svnjn-docs-check [path ...]   (defaults to README.md and docs/)
 */
final readonly class DocsCheckCommand
{
    public const array DEFAULT_PATHS = ['README.md', 'docs'];

    public function __construct(
        private DocsChecker $checker,
        private Output $output,
    ) {}

    /**
     * @param  list<string>  $arguments
     */
    public function run(array $arguments): int
    {
        try {
            $report = $this->checker->check($arguments === [] ? self::DEFAULT_PATHS : $arguments);
        } catch (StandardsException $exception) {
            $this->output->error($exception->getMessage());

            return 1;
        }

        if ($report->passed()) {
            $this->output->line(sprintf('Docs check passed: %d PHP example(s) are valid.', $report->checkedExamples));

            return 0;
        }

        foreach ($report->problems as $problem) {
            $this->output->error(sprintf('%s:%d  %s', $problem->file, $problem->line, $problem->message));
        }

        $this->output->error('');
        $this->output->error(sprintf(
            '%d problem(s) in %d PHP example(s). Fix the example, or put %s on the line before a fragment that is not meant to run.',
            count($report->problems),
            $report->checkedExamples,
            CodeBlockExtractor::SKIP_MARKER,
        ));

        return 1;
    }
}
