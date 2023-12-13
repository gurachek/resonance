<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use LogicException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * @template-implements IteratorAggregate<string,class-string>
 */
readonly class SingletonFunctionParametersIterator implements IteratorAggregate
{
    public function __construct(private ReflectionFunctionAbstract $reflectionFunction) {}

    /**
     * @return Generator<string,class-string>
     */
    public function getIterator(): Generator
    {
        foreach ($this->reflectionFunction->getParameters() as $constructorParameter) {
            $type = $constructorParameter->getType();

            if (!$type) {
                throw new LogicException(sprintf(
                    'Constructor parameter has no type: %s@%s',
                    $this->reflectionFunction instanceof ReflectionMethod
                        ? $this->reflectionFunction->getDeclaringClass()->getName()
                        : '',
                    $constructorParameter->getName(),
                ));
            }

            if (!($type instanceof ReflectionNamedType)) {
                throw new LogicException('Not a named type: '.$type::class);
            }

            if ($type->isBuiltin()) {
                throw new LogicException(sprintf(
                    'Parameter uses builtin type: %s in %s',
                    $type->getName(),
                    $this->reflectionFunction->getFileName(),
                ));
            }

            $typeClassName = $type->getName();

            if (!class_exists($typeClassName) && !interface_exists($typeClassName)) {
                throw new LogicException('Class does not exist: '.$typeClassName);
            }

            yield $constructorParameter->getName() => $typeClassName;
        }
    }
}
