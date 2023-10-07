<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;

interface TemplateStaticPageLayoutInterface extends TemplateInterface
{
    /**
     * @return Generator<string>
     */
    public function renderStaticPage(StaticPage $staticPage): Generator;
}
