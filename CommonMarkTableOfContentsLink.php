<?php

declare(strict_types=1);

namespace Resonance;

readonly class CommonMarkTableOfContentsLink
{
    public function __construct(
        public int $level,
        public string $slug,
        public string $text,
    ) {}
}
