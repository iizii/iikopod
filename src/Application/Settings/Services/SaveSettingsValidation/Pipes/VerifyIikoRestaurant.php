<?php

declare(strict_types=1);

namespace Application\Settings\Services\SaveSettingsValidation\Pipes;

use Closure;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Settings\OrganizationSetting;

final readonly class VerifyIikoRestaurant
{
    public function __construct(private IikoConnectorInterface $iikoConnector) {}

    public function handle(OrganizationSetting $settings, Closure $next): OrganizationSetting
    {
        return $next($settings);
    }
}
