<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Support;

use Svnjn\Standards\Docs\DocsCheckCommand;
use Svnjn\Standards\Docs\DocsChecker;
use Svnjn\Standards\Testing\BufferedOutput;
use Svnjn\Standards\Testing\FakeProcessRunner;

/**
 * A throwaway project with the docs checker wired to fakes.
 */
final readonly class DocsFixture
{
    public TemporaryDirectory $project;

    public FakeProcessRunner $processes;

    public BufferedOutput $output;

    public DocsChecker $checker;

    public DocsCheckCommand $command;

    public function __construct()
    {
        $this->project = TemporaryDirectory::create();
        $this->processes = new FakeProcessRunner();
        $this->output = new BufferedOutput();
        $this->checker = new DocsChecker($this->processes, $this->project->path);
        $this->command = new DocsCheckCommand($this->checker, $this->output);
    }

    public function snippet(int $number): string
    {
        return sprintf('%s/build/docs-check/example-%03d.php', $this->project->path, $number);
    }
}
