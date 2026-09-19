<?php

declare(strict_types=1);

namespace Svnjn\Standards\Docs;

/**
 * A fenced PHP example found in a markdown file.
 */
final readonly class CodeBlock
{
    public function __construct(
        public string $file,
        /** Markdown line of the first line of code (the line after the opening fence). */
        public int $line,
        public string $code,
    ) {}
}
