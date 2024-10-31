<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use InvalidArgumentException;
use Spatie\LaravelData\Data;

final class GetPaymentTypesRequestData extends Data
{
    /**
     * @param  array<array-key, string>  $organizationIds
     */
    public function __construct(
        public readonly array $organizationIds,
    ) {
        if (empty($this->organizationIds)) {
            throw new InvalidArgumentException(
                'Массив идентификаторов организаций должен содержать хотя бы один элемент',
            );
        }
    }
}
