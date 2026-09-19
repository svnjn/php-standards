<?php

declare(strict_types=1);

namespace Svnjn\Standards\Docs;

final readonly class DocsReport
{
    /**
     * @param  list<DocsProblem>  $problems
     */
    public function __construct(
        public int $checkedExamples,
        public array $problems,
    ) {}

    public function passed(): bool
    {
        return $this->problems === [];
    }
}
