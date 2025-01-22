<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class GetAvailableTerminalsResponseData extends ResponseData
{
    /**
     * @param  Items[]  $items
     */
    public function __construct(public readonly string $organizationId, public readonly array $items) {}
}
