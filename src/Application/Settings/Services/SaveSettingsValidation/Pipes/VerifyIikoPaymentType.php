<?php

declare(strict_types=1);

namespace Application\Settings\Services\SaveSettingsValidation\Pipes;

use Closure;
use Domain\Iiko\Exceptions\PaymentTypeNotFoundException;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Settings\OrganizationSetting;
use Domain\Settings\ValueObjects\PaymentType;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponse\GetPaymentTypesResponseData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\IIko\Requests\GetPaymentTypesRequest;

final readonly class VerifyIikoPaymentType
{
    public function __construct(private IikoConnectorInterface $iikoConnector, private IikoAuthenticator $authenticator) {}

    /**
     * @throws RequestException
     * @throws ConnectionException
     * @throws \Exception
     */
    public function handle(OrganizationSetting $settings, Closure $next): OrganizationSetting
    {
        /** @var LazyCollection<array-key, GetPaymentTypesResponseData> $response */
        $response = $this
            ->iikoConnector
            ->send(
                new GetPaymentTypesRequest(
                    new GetPaymentTypesRequestData([$settings->iikoRestaurantId->id]),
                    [
                        'Authorization' => sprintf(
                            'Bearer %s',
                            $this->authenticator->getAuthToken($settings->iikoApiKey),
                        ),
                    ],
                ),
            );

        $errorTypes = [];

        $settings->paymentTypeCollection->each(
            static function (PaymentType $paymentType) use (&$errorTypes, $response) {
                $exists = $response->contains(
                    static fn (GetPaymentTypesResponseData $item) => $item->code === $paymentType->iikoPaymentCode,
                );

                if (! $exists) {
                    $errorTypes[] = $paymentType->iikoPaymentCode;
                }
            },
        );

        if (! empty($errorTypes)) {
            throw new PaymentTypeNotFoundException(
                'В системе IIKO не существует следующих типов оплат: '.implode(', ', $errorTypes),
            );
        }

        return $next($settings);
    }
}
