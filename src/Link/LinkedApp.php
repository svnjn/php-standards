<?php

declare(strict_types=1);

namespace Svnjn\Standards\Link;

use JsonSerializable;
use Svnjn\Standards\Internal\ArrayReader;

/**
 * An app the package is linked into, and how the app required the package
 * before linking, so unlinking can put it back.
 */
final readonly class LinkedApp implements JsonSerializable
{
    public function __construct(
        public string $path,
        /** Null when the app did not require the package before linking. */
        public ?string $originalConstraint,
        public bool $dev,
    ) {}

    public static function fromReader(ArrayReader $input): self
    {
        return new self(
            path: $input->string('path'),
            originalConstraint: $input->optionalString('original_constraint'),
            dev: $input->bool('dev'),
        );
    }

    /**
     * @return array{path: string, original_constraint: ?string, dev: bool}
     */
    public function jsonSerialize(): array
    {
        return [
            'path' => $this->path,
            'original_constraint' => $this->originalConstraint,
            'dev' => $this->dev,
        ];
    }
}
