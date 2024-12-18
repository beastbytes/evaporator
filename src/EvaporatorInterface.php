<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator;

interface EvaporatorInterface
{
    /**
     * @param object $object The object to evaporate
     * @param array $properties The object properties to evaporate
     * @return array
     */
    public function evaporate(object $object, array $properties): array;
}
