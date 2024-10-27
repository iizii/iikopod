<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetRestaurantsResponse;

use Domain\WelcomeGroup\ValueObjects\Restaurant\PrinterPos;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetRestaurantsResponsePrinterPosData extends ResponseData
{
    public function __construct(public readonly string $host, public readonly string $uri) {}

    public function toDomainEntity(): PrinterPos
    {
        return new PrinterPos(
            $this->host,
            $this->uri,
        );
    }
}
