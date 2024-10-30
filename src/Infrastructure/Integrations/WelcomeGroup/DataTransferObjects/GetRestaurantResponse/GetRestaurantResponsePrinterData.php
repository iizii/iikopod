<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetRestaurantResponse;

use Domain\WelcomeGroup\ValueObjects\Restaurant\Printer;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetRestaurantResponsePrinterData extends ResponseData
{
    public function __construct(public readonly ?string $host, public readonly ?string $uri) {}

    public function toDomainEntity(): Printer
    {
        return new Printer(
            $this->host,
            $this->uri,
        );
    }
}
