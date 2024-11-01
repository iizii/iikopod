<?php

declare(strict_types=1);

namespace Domain\Settings\Interfaces;

use Domain\Settings\OrganizationSetting as DomainOrganizationSetting;
use Illuminate\Support\LazyCollection;
use Shared\Domain\ValueObjects\IntegerId;

interface OrganizationSettingRepositoryInterface
{
    /**
     * @return LazyCollection<array-key, DomainOrganizationSetting>
     */
    public function all(): LazyCollection;

    public function findById(IntegerId $integerId): ?DomainOrganizationSetting;
}
