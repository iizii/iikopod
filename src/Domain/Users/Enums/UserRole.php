<?php

declare(strict_types=1);

namespace Domain\Users\Enums;

enum UserRole: string
{
    case USER = 'user';

    case ADMIN = 'admin';

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN->value;
    }
}
