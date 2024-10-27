<?php

declare(strict_types=1);

namespace Application\Settings\Services\SaveSettingsValidation;

use Application\Settings\Services\SaveSettingsValidation\Pipes\VerifyIikoPaymentType;
use Application\Settings\Services\SaveSettingsValidation\Pipes\VerifyIikoRestaurant;
use Application\Settings\Services\SaveSettingsValidation\Pipes\VerifyWelcomeGroupRestaurant;
use Application\Settings\Services\SaveSettingsValidation\Pipes\VerifyWelcomeGroupWorkshop;
use Domain\Settings\OrganizationSetting;
use Illuminate\Pipeline\Pipeline;

final class SaveSettingsValidationPipeline
{
    /**
     * @var array<class-string>
     */
    private static array $pipes = [
        VerifyIikoRestaurant::class,
        VerifyWelcomeGroupRestaurant::class,
        VerifyWelcomeGroupWorkshop::class,
        VerifyIikoPaymentType::class,
    ];

    public function __construct(private readonly Pipeline $pipeline) {}

    public function handle(OrganizationSetting $settings): void
    {
        $this
            ->pipeline
            ->send($settings)
            ->through(self::$pipes)
            ->thenReturn();
    }
}
