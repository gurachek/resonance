<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\StreamInterface;
use Stringable;

abstract readonly class HttpResponder implements HttpResponderInterface
{
    public function createStream(string|Stringable $contents): StreamInterface
    {
        return new PsrStringStream($contents);
    }
}
