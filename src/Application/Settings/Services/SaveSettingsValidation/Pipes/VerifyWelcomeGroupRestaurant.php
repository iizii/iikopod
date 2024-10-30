<?php

declare(strict_types=1);

namespace Application\Settings\Services\SaveSettingsValidation\Pipes;

use Closure;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Settings\OrganizationSetting;
use Domain\WelcomeGroup\Exceptions\RestaurantNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Infrastructure\Integrations\WelcomeGroup\Requests\GetRestaurantRequest;
use Symfony\Component\HttpFoundation\Response;

final readonly class VerifyWelcomeGroupRestaurant
{
    public function __construct(private WelcomeGroupConnectorInterface $welcomeGroupConnector) {}

    /**
     * @throws ConnectionException
     * @throws RequestException
     * @throws RestaurantNotFoundException
     * @throws \Throwable
     */
    public function handle(OrganizationSetting $settings, Closure $next): OrganizationSetting
    {
        try {
            $this->welcomeGroupConnector->send(
                new GetRestaurantRequest($settings->welcomeGroupRestaurantId),
            );
        } catch (\Throwable $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                throw new RestaurantNotFoundException('Запрос ресторана по ID не вернул результатов');
            }

            throw $exception;
        }

        return $next($settings);
    }
}
