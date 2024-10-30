<?php

declare(strict_types=1);

namespace Application\Settings\Services\SaveSettingsValidation\Pipes;

use Closure;
use Domain\Iiko\Exceptions\PaymentTypeNotFoundException;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Settings\OrganizationSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponseData;
use Infrastructure\Integrations\IIko\Requests\AuthorizationRequest;
use Infrastructure\Integrations\IIko\Requests\GetOrganizationsRequest;
use Infrastructure\Integrations\IIko\Requests\GetPaymentTypesRequest;

final class VerifyIikoPaymentType
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

        /** @var Collection<int, GetPaymentTypesResponseData> $paymentTypesRes */
        $paymentTypesRes = collect($this
            ->iikoConnector
            ->send(
                new GetPaymentTypesRequest(
                    new GetPaymentTypesRequestData([$settings->iikoRestaurantId->id]),
                    ['Authorization' => 'Bearer ' . Cache::get($settings->iikoApiKey)]
                )
            ));

        $errorTypes = [];

        foreach ($settings->paymentTypeCollection as $paymentType) {
            // Проверяем, есть ли совпадение в коллекции по свойству 'code'
            $exists = $paymentTypesRes->contains(fn(GetPaymentTypesResponseData $item) => $item->code === $paymentType->iikoPaymentCode);

            if (!$exists) {
                $errorTypes[] = $paymentType->iikoPaymentCode;
            }
        }

        if (!empty($errorTypes)) {
            throw new PaymentTypeNotFoundException('В системе IIKO не существует следующих типов оплат: ' . implode(', ', $errorTypes));
        }

        return $next($settings);
    }
}
