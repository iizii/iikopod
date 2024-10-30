<?php

declare(strict_types=1);

namespace Application\Settings\Services\SaveSettingsValidation\Pipes;

use Closure;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Settings\OrganizationSetting;
use Domain\WelcomeGroup\Exceptions\RestaurantNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationResponseData;
use Infrastructure\Integrations\IIko\Requests\AuthorizationRequest;
use Infrastructure\Integrations\IIko\Requests\GetOrganizationsRequest;

final readonly class VerifyIikoRestaurant
{
    public function __construct(private IikoConnectorInterface $iikoConnector) {}

    /**
     * @throws RequestException
     * @throws ConnectionException
     * @throws \Exception
     */
    public function handle(OrganizationSetting $settings, Closure $next): OrganizationSetting
    {
        if (!Cache::has($settings->iikoApiKey)) {
            /** @var AuthorizationResponseData $authRes */
            $authRes = $this
                ->iikoConnector
                ->send(new AuthorizationRequest($settings->iikoApiKey));
            $token = $authRes->token;
            Cache::put(
                $settings->iikoApiKey,
                $token,
                3000
            );
        }
        try {
            /** @var GetOrganizationResponseData[] $orgRes */
            $orgRes = $this
                ->iikoConnector
                ->send(
                    new GetOrganizationsRequest(
                        new GetOrganizationRequestData([$settings->iikoRestaurantId->id], true, false),
                        ['Authorization' => 'Bearer ' . Cache::get($settings->iikoApiKey)]
                    )
                );

            $organization = null;

            foreach ($orgRes as $org) {
                if ($org->id === $settings->iikoRestaurantId->id) {
                    $organization = $org;
                    break; // Завершить цикл после нахождения первого совпадения
                }
            }

            if (!$organization) {
                throw new RestaurantNotFoundException('Организация не найдена в IIKO');
            }
        } catch (RestaurantNotFoundException $e) {
            throw new RestaurantNotFoundException($e->getMessage());
        }

        return $next($settings);
    }
}
