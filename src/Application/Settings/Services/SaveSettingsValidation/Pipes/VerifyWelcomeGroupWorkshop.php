<?php

declare(strict_types=1);

namespace Application\Settings\Services\SaveSettingsValidation\Pipes;

use Closure;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Settings\OrganizationSetting;
use Domain\WelcomeGroup\Exceptions\WorkshopNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Infrastructure\Integrations\WelcomeGroup\Requests\Workshop\GetWorkshopRequest;
use Symfony\Component\HttpFoundation\Response;

final readonly class VerifyWelcomeGroupWorkshop
{
    public function __construct(private WelcomeGroupConnectorInterface $welcomeGroupConnector) {}

    /**
     * @throws RequestException
     * @throws ConnectionException
     * @throws WorkshopNotFoundException
     * @throws \Throwable
     */
    public function handle(OrganizationSetting $settings, Closure $next): OrganizationSetting
    {
        try {
            $this->welcomeGroupConnector->send(
                new GetWorkshopRequest($settings->welcomeGroupDefaultWorkshopId),
            );
        } catch (\Throwable $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                throw new WorkshopNotFoundException('Запрос цеха по ID не вернул результатов');
            }

            throw $exception;
        }

        return $next($settings);
    }
}
