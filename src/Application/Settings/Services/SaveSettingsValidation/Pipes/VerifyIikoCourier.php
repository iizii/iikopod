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
use Infrastructure\Integrations\IIko\DataTransferObjects\GetActiveOrganizationCouriersRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetActiveOrganizationCouriersResponseData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\IIko\Requests\GetActiveOrganizationCouriersRequest;

final readonly class VerifyIikoCourier
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
            /** @var LazyCollection<array-key, GetActiveOrganizationCouriersResponseData> $response */
            $response = $this
                ->iikoConnector
                ->send(
                    new GetActiveOrganizationCouriersRequest(
                        new GetActiveOrganizationCouriersRequestData([(string) $settings->iikoRestaurantId->id]),
                        $this->authenticator->getAuthToken($settings->iikoApiKey),
                    ),
                );

            $response->firstOrFail(
                static function (GetActiveOrganizationCouriersResponseData $responseData) use ($settings): bool {
                    return $responseData->courierId === $settings->iikoCourierId->id;
                },
            );
        } catch (ItemNotFoundException $exception) {
            throw new RestaurantNotFoundException('Курьер для ПОД не найден в IIKO, либо его сессия неактивна чего нельзя допускать для корректной работы системы');
        } catch (RestaurantNotFoundException $e) {
            throw new RestaurantNotFoundException($e->getMessage());
        }

        return $next($settings);
    }
}
