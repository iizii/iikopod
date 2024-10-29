<?php

declare(strict_types=1);

namespace Presentation\Admin\Resources\OrganizationSettingResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Presentation\Admin\Resources\OrganizationSettingResource;

final class ListOrganizationSettings extends ListRecords
{
    protected static string $resource = OrganizationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
