<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Settings\Repositories;

use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting as DomainOrganizationSetting;
use Illuminate\Support\LazyCollection;
use Infrastructure\Persistence\Eloquent\Settings\Models\OrganizationSetting;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<OrganizationSetting>
 */
final class OrganizationSettingRepository extends AbstractPersistenceRepository implements OrganizationSettingRepositoryInterface
{
    /**
     * @return LazyCollection<array-key, DomainOrganizationSetting>
     */
    public function all(): LazyCollection
    {
        return $this
            ->query()
            ->cursor()
            ->map(static fn (OrganizationSetting $organizationSetting): DomainOrganizationSetting => $organizationSetting->toDomainEntity());
    }

    public function findById(IntegerId $integerId): ?DomainOrganizationSetting
    {
        return $this
            ->query()
            ->find($integerId->id)
            ?->toDomainEntity();
    }
}
