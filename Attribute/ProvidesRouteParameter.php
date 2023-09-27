<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ProvidesRouteParameter extends BaseAttribute
{
    /**
     * @param class-string $class
     */
    public function __construct(public string $class) {}
}