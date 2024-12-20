<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator;

use AllowDynamicProperties;
use BeastBytes\Evaporator\AttributeHandling\ColumnAttributesHandler;
use ReflectionClass;
use Yiisoft\Hydrator\AttributeHandling\ResolverFactory\ReflectionAttributeResolverFactory;

#[AllowDynamicProperties]
final class Evaporator implements EvaporatorInterface
{
    private ColumnAttributesHandler $columnAttributesHandler;

    public function __construct(
    )
    {
        $attributeResolverFactory ??= new ReflectionAttributeResolverFactory();
        $this->columnAttributesHandler = new ColumnAttributesHandler($attributeResolverFactory, $this);
    }

    public function evaporate(object $object, array $properties): array
    {
        $columns = [];

        $reflectionClass = new ReflectionClass($object);
        $reflectionProperties = $reflectionClass->getProperties();

        foreach ($reflectionProperties as $property) {
            $propertyName = $property->getName();
            if (array_key_exists($propertyName, $properties)) {
                $result = $this->columnAttributesHandler->handle($object, $property);
                if ($result->isResolved()) {
                    $columns[$properties[$propertyName]] = $result->getValue();
                }
            }
        }

        return $columns;
    }
}
