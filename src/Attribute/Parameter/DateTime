<?php

declare(strict_types=1);

namespace BeastBytes\Evaporator\Attribute\Parameter;

use Attribute;
use BeastBytes\Evaporator\AttributeHandling\ColumnAttributeResolveContext;
use BeastBytes\Evaporator\AttributeHandling\ColumnAttributeResolverInterface;
use DateTimeInterface;
use IntlDateFormatter;
use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeInterface;
use Yiisoft\Hydrator\Attribute\Parameter\ParameterAttributeResolverInterface;
use Yiisoft\Hydrator\Attribute\Parameter\ToDateTime;
use Yiisoft\Hydrator\Attribute\Parameter\ToDateTimeResolver;
use Yiisoft\Hydrator\AttributeHandling\ParameterAttributeResolveContext;
use Yiisoft\Hydrator\Result;

/**
 * Converts between a DateTimeInterface object and its scalar representation
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final class DateTime implements
    ParameterAttributeInterface,
    ParameterAttributeResolverInterface,
    ColumnAttributeResolverInterface
{
    /**
     * @psalm-param IntlDateFormatter|null $dateType
     * @psalm-param IntlDateFormatter|null $timeType
     * @psalm-param non-empty-string|null $timeZone
     */
    public function __construct(
        private readonly ?string $format = null,
        private readonly int $dateType = IntlDateFormatter::SHORT,
        private readonly int $timeType = IntlDateFormatter::SHORT,
        private readonly ?string $timeZone = null,
        private readonly ?string $locale = null,
    ) {
    }

    public function getColumnValue(
        ParameterAttributeInterface $attribute,
        ColumnAttributeResolveContext $context
    ): Result
    {
        if ($context->isResolved()) {
            $resolvedValue = $context->getResolvedValue();

            if ($resolvedValue instanceof DateTimeInterface) {
                if (str_starts_with($this->format, 'php:')) {
                    return Result::success(
                        $resolvedValue->format(substr($this->format, 4))
                    );
                } else {
                    $formatter = $this->format === null
                        ? new IntlDateFormatter(
                            $this->locale,
                            $this->dateType,
                            $this->timeType,
                            $this->timeZone
                        )
                        : new IntlDateFormatter(
                            $this->locale,
                            IntlDateFormatter::NONE,
                            IntlDateFormatter::NONE,
                            $this->timeZone,
                            pattern: $this->format
                        );

                    return Result::success($formatter->format($resolvedValue));
                }
            }
        }

        return Result::fail();
    }

    public function getParameterValue(
        ParameterAttributeInterface $attribute,
        ParameterAttributeResolveContext $context
    ): Result
    {
        $attribute = new ToDateTime(
            $this->format,
            $this->dateType,
            $this->timeType,
            $this->timeZone,
            $this->locale
        );
        $dateTimeResolver = new ToDateTimeResolver(
            $this->format,
            $this->dateType,
            $this->timeType,
            $this->timeZone,
            $this->locale
        );
        return $dateTimeResolver->getParameterValue($attribute, $context);
    }

    public function getResolver(): self
    {
        return $this;
    }
}
