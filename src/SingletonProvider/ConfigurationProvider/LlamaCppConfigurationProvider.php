<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\LlamaCppConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<LlamaCppConfiguration, object{
 *     api_key: null|string,
 *     completion_token_timeout: float,
 *     host: string,
 *     port: int,
 *     scheme: string,
 * }>
 */
#[Singleton(provides: LlamaCppConfiguration::class)]
final readonly class LlamaCppConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'llamacpp';
    }

    protected function makeSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'api_key' => [
                    'type' => ['null', 'string'],
                    'minLength' => 1,
                    'default' => null,
                ],
                'host' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'completion_token_timeout' => [
                    'type' => 'number',
                    'default' => 1.0,
                ],
                'port' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 65535,
                ],
                'scheme' => [
                    'type' => 'string',
                    'enum' => ['http', 'https'],
                    'default' => 'http',
                ],
            ],
            'required' => ['host', 'port'],
        ]);
    }

    protected function provideConfiguration($validatedData): LlamaCppConfiguration
    {
        return new LlamaCppConfiguration(
            apiKey: $validatedData->api_key,
            completionTokenTimeout: $validatedData->completion_token_timeout,
            host: $validatedData->host,
            port: $validatedData->port,
            scheme: $validatedData->scheme,
        );
    }
}