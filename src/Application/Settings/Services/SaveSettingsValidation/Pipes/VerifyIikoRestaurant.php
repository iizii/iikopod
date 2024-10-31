<?php

declare(strict_types=1);

namespace Application\Settings\Services\SaveSettingsValidation\Pipes;

use Closure;
use Domain\Iiko\Exceptions\RestaurantNotFoundException;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Settings\OrganizationSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationsResponse\GetOrganizationResponseData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\IIko\Requests\GetOrganizationsRequest;

final readonly class VerifyIikoRestaurant
{
    public function __construct(private IikoConnectorInterface $iikoConnector, private IikoAuthenticator $authenticator) {}

    /**
     * @throws RequestException
     * @throws ConnectionException
     * @throws \Exception
     */
    public function handle(OrganizationSetting $settings, Closure $next): OrganizationSetting
    {
        try {
            /** @var LazyCollection<array-key, GetOrganizationResponseData> $response */
            $response = $this
                ->iikoConnector
                ->send(
                    new GetOrganizationsRequest(
                        new GetOrganizationRequestData([(string) $settings->iikoRestaurantId->id], true, false),
                        $this->authenticator->getAuthToken($settings->iikoApiKey),
                    ),
                );

            $response->firstOrFail(
                static function (GetOrganizationResponseData $responseData) use ($settings): bool {
                    return $responseData->id === $settings->iikoRestaurantId->id;
                },
            );
        } catch (ItemNotFoundException $exception) {
            throw new RestaurantNotFoundException('Организация не найдена в IIKO');
        } catch (RestaurantNotFoundException $e) {
            throw new RestaurantNotFoundException($e->getMessage());
        }

        return $next($settings);
    }
}
