<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator\Attribute\Parameter;

use Attribute;
use BeastBytes\Evaporator\AttributeHandling\ColumnAttributeResolveContext;
use BeastBytes\Evaporator\AttributeHandling\ColumnAttributeResolverInterface;
use Ramsey\Uuid\Uuid as U;
use Ramsey\Uuid\UuidInterface;
use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeInterface;
use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeResolverInterface;
use Yiisoft\Hydrator\AttributeHandling\Exception\UnexpectedAttributeException;
use Yiisoft\Hydrator\AttributeHandling\ParameterAttributeResolveContext;
use Yiisoft\Hydrator\Result;

/**
 * Converts between a UuidInterface object and its scalar representation
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final class Uuid implements
    ParameterAttributeInterface,
    ParameterAttributeResolverInterface,
    ColumnAttributeResolverInterface
{
    public function __construct(private UuidFormat $format)
    {
    }

    public function getColumnValue(
        ParameterAttributeInterface $attribute,
        ColumnAttributeResolveContext $context
    ): Result
    {
        if ($context->isResolved()) {
            $resolvedValue = $context->getResolvedValue();

            if ($resolvedValue instanceof UuidInterface) {
                $uuid = match ($this->format) {
                    UuidFormat::Bytes => $resolvedValue->getBytes(),
                    UuidFormat::Integer => $resolvedValue->getInteger(),
                    UuidFormat::String => $resolvedValue->toString(),
                };

                return Result::success($uuid);
            }
        }

        return Result::fail();
    }

    public function getParameterValue(
        ParameterAttributeInterface $attribute,
        ParameterAttributeResolveContext $context
    ): Result
    {
        if (!$attribute instanceof Uuid) {
            throw new UnexpectedAttributeException(Uuid::class, $attribute);
        }

        if ($context->isResolved()) {
            $resolvedValue = $context->getResolvedValue();

            if (is_string($resolvedValue) && !empty($resolvedValue)) {
                $uuid = match ($this->format) {
                    UuidFormat::Bytes => U::fromBytes($resolvedValue),
                    UuidFormat::Integer => U::fromInteger($resolvedValue),
                    UuidFormat::String => U::fromString($resolvedValue),
                };

                return Result::success($uuid);
            }
        }

        return Result::fail();
    }

    public function getResolver(): self
    {
        return $this;
    }
}
