<?php

declare(strict_types=1);

namespace Presentation\Admin\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Presentation\Admin\Resources\UserResource;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
