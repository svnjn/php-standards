<?php

declare(strict_types=1);

namespace Svnjn\Standards\Docs;

/**
 * Finds ```php fences in markdown. A fence directly preceded by
 * <!-- docs-check: skip --> is left out.
 */
final class CodeBlockExtractor
{
    public const string SKIP_MARKER = '<!-- docs-check: skip -->';

    private const string OPENING_FENCE = '/^\s*(?<fence>`{3,}|~{3,})\s*(?<language>[\w+-]*)/';

    /**
     * @return list<CodeBlock>
     */
    public function extract(string $file, string $markdown): array
    {
        $blocks = [];
        $fence = null;
        $isPhp = false;
        $skip = false;
        $startLine = 0;
        $code = [];
        $previousLine = '';

        $lines = preg_split('/\R/', $markdown);

        foreach ($lines === false ? [] : $lines as $index => $line) {
            if ($fence === null) {
                if (preg_match(self::OPENING_FENCE, $line, $matches) === 1) {
                    $fence = $matches['fence'];
                    $isPhp = strtolower($matches['language']) === 'php';
                    $skip = trim($previousLine) === self::SKIP_MARKER;
                    $startLine = $index + 2;
                    $code = [];
                } elseif (trim($line) !== '') {
                    $previousLine = $line;
                }

                continue;
            }

            if ($this->closes($line, $fence)) {
                if ($isPhp && ! $skip) {
                    $blocks[] = new CodeBlock($file, $startLine, implode("\n", $code));
                }

                $fence = null;
                $previousLine = '';

                continue;
            }

            $code[] = $line;
        }

        return $blocks;
    }

    private function closes(string $line, string $fence): bool
    {
        $pattern = sprintf('/^\s*%s{%d,}\s*$/', preg_quote($fence[0], '/'), strlen($fence));

        return preg_match($pattern, $line) === 1;
    }
}
