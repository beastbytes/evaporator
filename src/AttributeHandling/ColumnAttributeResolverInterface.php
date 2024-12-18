<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator\AttributeHandling;

use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeInterface;
use Yiisoft\Hydrator\Result;

/**
 * An interface for resolvers of attributes that implement {@see ParameterAttributeInterface}.
 */
interface ColumnAttributeResolverInterface
{
    /**
     * Returns the resolved from specified attribute value object.
     *
     * @param ParameterAttributeInterface $attribute The attribute to be resolved.
     * @param ColumnAttributeResolveContext $context The context of value resolving from attribute.
     *
     * @return Result The resolved from specified attribute value object.
     */
    public function getColumnValue(
        ParameterAttributeInterface $attribute,
        ColumnAttributeResolveContext $context
    ): Result;
}
