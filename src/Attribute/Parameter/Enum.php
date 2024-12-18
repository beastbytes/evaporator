<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator\Attribute\Parameter;

use Attribute;
use BeastBytes\Evaporator\AttributeHandling\ColumnAttributeResolveContext;
use BeastBytes\Evaporator\AttributeHandling\ColumnAttributeResolverInterface;
use Exception;
use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeInterface;
use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeResolverInterface;
use Yiisoft\Hydrator\AttributeHandling\Exception\UnexpectedAttributeException;
use Yiisoft\Hydrator\AttributeHandling\ParameterAttributeResolveContext;
use Yiisoft\Hydrator\Result;

/**
 * Converts between an Enum and its name
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final class Enum implements
    ParameterAttributeInterface,
    ParameterAttributeResolverInterface,
    ColumnAttributeResolverInterface
{
    public function __construct(private string $enum)
    {
    }

    /**
     * @throws Exception
     */
    public function getColumnValue(
        ParameterAttributeInterface $attribute,
        ColumnAttributeResolveContext $context
    ): Result
    {
        if ($context->isResolved()) {
            $resolvedValue = $context->getResolvedValue();

            if ($resolvedValue !== null) {
                return Result::success($resolvedValue->name);
            }
        }

        return Result::fail();
    }

    /**
     * @throws Exception
     */
    public function getParameterValue(
        ParameterAttributeInterface $attribute,
        ParameterAttributeResolveContext $context
    ): Result
    {
        if (!$attribute instanceof Enum) {
            throw new UnexpectedAttributeException(Enum::class, $attribute);
        }

        if ($context->isResolved()) {
            $resolvedValue = $context->getResolvedValue();

            if (
                is_string($resolvedValue)
                && in_array(
                    $resolvedValue,
                    array_column($this->enum::cases(), 'name')
                )
            ) {
                return Result::success($this->enum::{$resolvedValue});
            }
        }

        return Result::fail();
    }

    public function getResolver(): self
    {
        return $this;
    }
}
