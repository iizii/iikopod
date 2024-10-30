<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko;

use Carbon\CarbonImmutable;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Infrastructure\Integrations\IIko\Requests\AuthorizationRequest;

final readonly class IikoAuthenticator
{
    private const CACHE_KEY = 'iiko_auth_token';

    public function __construct(
        private CacheRepository $cacheRepository,
        private CarbonImmutable $dateTime,
        private IikoConnectorInterface $iikoConnector,
    ) {}

    public function getAuthToken(string $iikoApiKey): string
    {
        return $this->cacheRepository->remember(
            self::CACHE_KEY,
            $this->dateTime->addMinutes(30),
            function () use ($iikoApiKey): string {
                /** @var AuthorizationResponseData $response */
                $response = $this->iikoConnector->send(new AuthorizationRequest($iikoApiKey));

                return $response->token;
            },
        );
    }
}
