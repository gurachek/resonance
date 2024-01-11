<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class OpenAPISchemaBuilder
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private HttpControllerReflectionMethodCollection $httpControllerReflectionMethodCollection,
        private OpenAPIConfiguration $openAPIConfiguration,
        private OpenAPIPathItemCollection $openAPIPathItemCollection,
        private OpenAPIRouteParameterExtractorAggregate $openAPIRouteParameterExtractorAggregate,
        private OpenAPISchemaComponents $openAPISchemaComponents,
    ) {}

    public function buildSchema(OpenAPISchemaSymbolInterface $schemaSymbol): OpenAPISchema
    {
        return new OpenAPISchema(
            $this->applicationConfiguration,
            $this->httpControllerReflectionMethodCollection,
            $this->openAPIConfiguration,
            $this->openAPIPathItemCollection,
            $this->openAPIRouteParameterExtractorAggregate,
            $this->openAPISchemaComponents,
            $schemaSymbol,
        );
    }
}
