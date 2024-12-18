<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator\Attribute\Parameter;

enum UuidFormat
{
    case Bytes;
    case Integer;
    case String;
}
