<?php

declare(strict_types=1);

namespace Application\Settings\Services\SaveSettingsValidation\Pipes;

use Closure;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Settings\OrganizationSetting;
use Infrastructure\Integrations\WelcomeGroup\Requests\GetRestaurantsRequest;

final class VerifyWelcomeGroupRestaurant
{
    public function __construct(private WelcomeGroupConnectorInterface $welcomeGroupConnector) {}

    public function handle(OrganizationSetting $settings, Closure $next): OrganizationSetting
    {
        $this->welcomeGroupConnector->send(new GetRestaurantsRequest());

        return $next($settings);
    }
}
