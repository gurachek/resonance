<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class SessionConfiguration
{
    /**
     * @param non-empty-string      $cookieName
     * @param 'lax'|'none'|'strict' $cookieSameSite
     */
    public function __construct(
        public string $cookieName,
        public int $cookieLifespan,
        public string $cookieSameSite,
        public string $redisConnectionPool,
    ) {}
}
