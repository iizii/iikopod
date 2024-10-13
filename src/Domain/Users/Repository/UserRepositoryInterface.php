<?php

declare(strict_types=1);

namespace Domain\Users\Repository;

use Domain\Users\Models\User;

interface UserRepositoryInterface
{
    public function save(User $user): User;
}
