<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator\AttributeHandling;

use BeastBytes\Evaporator\EvaporatorInterface;
use LogicException;
use ReflectionAttribute;
use ReflectionParameter;
use ReflectionProperty;
use RuntimeException;
use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeInterface;
use Yiisoft\Hydrator\AttributeHandling\ResolverFactory\AttributeResolverFactoryInterface;
use Yiisoft\Hydrator\Result;

final class ColumnAttributesHandler
{
    public function __construct(
        private AttributeResolverFactoryInterface $attributeResolverFactory,
        private ?EvaporatorInterface $evaporator = null,
    ) {
    }

    public function handle(
        ?object $object,
        ReflectionParameter|ReflectionProperty $parameter
    ): Result {
        if ($this->evaporator === null) {
            throw new LogicException('Evaporator is not set in parameter attributes handler.');
        }

        $resolveResult = Result::success($parameter->getValue($object));

        $reflectionAttributes = $parameter
            ->getAttributes(ParameterAttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($reflectionAttributes as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();

            $resolver = $this->attributeResolverFactory->create($attribute);
            if (!$resolver instanceof ColumnAttributeResolverInterface) {
                throw new RuntimeException(
                    sprintf(
                        'Parameter attribute resolver "%s" must implement "%s".',
                        get_debug_type($resolver),
                        ColumnAttributeResolverInterface::class,
                    ),
                );
            }

            $context = new ColumnAttributeResolveContext($parameter, $resolveResult, $object, $this->evaporator);

            $tryResolveResult = $resolver->getColumnValue($attribute, $context);
            if ($tryResolveResult->isResolved()) {
                $resolveResult = $tryResolveResult;
            }
        }

        return $resolveResult;
    }

    public function withEvaporator(EvaporatorInterface $evaporator): self
    {
        $new = clone $this;
        $new->evaporator = $evaporator;
        return $new;
    }
}
