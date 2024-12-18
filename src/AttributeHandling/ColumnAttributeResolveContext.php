<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator\AttributeHandling;

use BeastBytes\Evaporator\EvaporatorInterface;
use LogicException;
use ReflectionParameter;
use ReflectionProperty;
use Yiisoft\Hydrator\Result;

/**
 * Holds attribute resolving context data.
 */
final readonly class ColumnAttributeResolveContext
{
    /**
     * @param ReflectionParameter|ReflectionProperty $parameter Resolved parameter or property reflection.
     * @param Result $resolveResult The resolved value object.
     * @param object $object Object to be used for resolving.
     * @param ?EvaporatorInterface $evaporator Evaporator instance.
     */
    public function __construct(
        private ReflectionParameter|ReflectionProperty $parameter,
        private Result $resolveResult,
        private object $object,
        private ?EvaporatorInterface $evaporator = null,
    ) {
    }

    /**
     * Get resolved parameter or property reflection.
     *
     * @return ReflectionParameter|ReflectionProperty Resolved parameter or property reflection.
     */
    public function getParameter(): ReflectionParameter|ReflectionProperty
    {
        return $this->parameter;
    }

    /**
     * Get whether the value for object property is resolved already.
     *
     * @return bool Whether the value for object property is resolved.
     */
    public function isResolved(): bool
    {
        return $this->resolveResult->isResolved();
    }

    /**
     * Get the resolved value.
     *
     * When value is not resolved returns `null`. But `null` can be is resolved value, use {@see isResolved()} for check
     * the value is resolved or not.
     *
     * @return mixed The resolved value.
     */
    public function getResolvedValue(): mixed
    {
        return $this->resolveResult->getValue();
    }

    /**
     * @return object Data to be used for resolving.
     */
    public function getObject(): object
    {
        return $this->object;
    }

    public function getEvaporator(): EvaporatorInterface
    {
        if ($this->evaporator === null) {
            throw new LogicException('Evaporator is not set in parameter attribute resolve context.');
        }

        return $this->evaporator;
    }
}
