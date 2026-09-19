<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Support;

use Svnjn\Standards\Link\LinkCommand;
use Svnjn\Standards\Link\Linker;
use Svnjn\Standards\Testing\BufferedOutput;
use Svnjn\Standards\Testing\FakeProcessRunner;

/**
 * A workspace with a package (svnjn/demo) and an app, and a linker wired to fakes.
 */
final readonly class LinkFixture
{
    public TemporaryDirectory $workspace;

    public string $package;

    public string $app;

    public FakeProcessRunner $processes;

    public BufferedOutput $output;

    public Linker $linker;

    public LinkCommand $command;

    public function __construct(string $appComposerJson = '{"name": "acme/app"}')
    {
        $this->workspace = TemporaryDirectory::create();
        $this->package = $this->workspace->path . '/demo';
        $this->app = $this->workspace->path . '/app';
        $this->workspace->write('demo/composer.json', '{"name": "svnjn/demo"}');
        $this->workspace->write('app/composer.json', $appComposerJson);
        $this->processes = new FakeProcessRunner();
        $this->output = new BufferedOutput();
        $this->linker = new Linker($this->processes, $this->output);
        $this->command = new LinkCommand($this->linker, $this->output, $this->package);
    }
}
