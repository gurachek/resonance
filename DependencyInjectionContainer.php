<?php

declare(strict_types=1);

namespace Resonance;

use DomainException;
use Generator;
use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;

readonly class DependencyInjectionContainer
{
    public SingletonContainer $singletons;

    public function __construct()
    {
        $this->singletons = new SingletonContainer();
    }

    /**
     * @template TSingleton as object
     *
     * @param class-string<TSingleton> $class
     *
     * @return TSingleton
     */
    public function make(string $class): object
    {
        $reflectionClass = new ReflectionClass($class);
        $parameters = iterator_to_array($this->buildClassParameters($reflectionClass));

        /**
         * @var null|TSingleton $instance
         */
        $instance = $reflectionClass->newInstanceArgs($parameters);

        if (is_null($instance)) {
            throw new LogicException('Unable to instantiate singleton object');
        }

        return $instance;
    }

    private function buildClassParameters(ReflectionClass $reflectionClass): Generator
    {
        foreach (new ConstructorParametersIterator($reflectionClass) as $parameter) {
            yield $parameter->getName() => $this->getParameterValue($parameter);
        }
    }

    private function getParameterValue(ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();

        if (is_null($type)) {
            return $this->getUntypedParameterValue($parameter);
        }

        return $this->getTypedParameterValue($parameter, $type);
    }

    private function getTypedParameterValue(
        ReflectionParameter $parameter,
        ReflectionType $type,
    ): mixed {
        if (!($type instanceof ReflectionNamedType)) {
            throw new DomainException('Unsupported parameter type: '.$type::class);
        }

        if ($type->isBuiltin()) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            $this->reportError('Parameter is a built-in type without a default value', $parameter, $type);
        }

        if (!$this->singletons->has($type->getName()) && $parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if (!$this->singletons->has($type->getName())) {
            $this->reportError('Singleton for parameter is not set', $parameter, $type);
        }

        return $this->singletons->get($type->getName());
    }

    private function getUntypedParameterValue(ReflectionParameter $parameter): mixed
    {
        if (!$parameter->isDefaultValueAvailable()) {
            $this->reportError('Parameter is not typed and no default value is available', $parameter);
        }

        return $parameter->getDefaultValue();
    }

    private function reportError(
        string $errorMessage,
        ReflectionParameter $parameter,
        ?ReflectionNamedType $type = null,
    ): never {
        throw new LogicException(sprintf(
            '%s. Trying to build: %s(%s$%s)',
            $errorMessage,
            (string) $parameter->getDeclaringClass()?->getName(),
            $type ? $type->getName().' ' : '',
            $parameter->getName(),
        ));
    }
}
