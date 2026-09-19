<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Docs;

use Svnjn\Standards\Docs\CodeBlock;
use Svnjn\Standards\Docs\CodeBlockExtractor;

it('extracts php fences with the markdown line of their first line of code', function (): void {
    $markdown = <<<'MD'
        # Usage

        ```php
        $a = 1;
        $b = 2;
        ```

        Text.

        ```php
        echo $a;
        ```
        MD;

    expect((new CodeBlockExtractor())->extract('docs/usage.md', $markdown))->toEqual([
        new CodeBlock('docs/usage.md', 4, "\$a = 1;\n\$b = 2;"),
        new CodeBlock('docs/usage.md', 11, 'echo $a;'),
    ]);
});

it('ignores fences in other languages', function (): void {
    $markdown = "```bash\ncomposer test\n```\n\n```json\n{}\n```\n\n```\nplain\n```";

    expect((new CodeBlockExtractor())->extract('README.md', $markdown))->toBe([]);
});

it('skips a php fence directly preceded by the skip marker', function (): void {
    $markdown = "<!-- docs-check: skip -->\n```php\nfragment(\n```\n\n```php\n\$ok = true;\n```";

    expect((new CodeBlockExtractor())->extract('README.md', $markdown))->toEqual([
        new CodeBlock('README.md', 7, '$ok = true;'),
    ]);
});

it('handles tilde fences and longer fences that contain backticks', function (): void {
    $markdown = "~~~php\n\$a = 1;\n~~~\n\n````php\n\$b = '```';\n````";

    expect((new CodeBlockExtractor())->extract('README.md', $markdown))->toEqual([
        new CodeBlock('README.md', 2, '$a = 1;'),
        new CodeBlock('README.md', 6, "\$b = '```';"),
    ]);
});

it('ignores an unclosed fence', function (): void {
    expect((new CodeBlockExtractor())->extract('README.md', "```php\n\$a = 1;"))->toBe([]);
});
