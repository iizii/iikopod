<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Repositories;

use Domain\Users\Models\User;
use Domain\Users\Repositories\UserRepositoryInterface;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

final class UserRepository extends AbstractPersistenceRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return $this->query()->find($id);
    }

    public function save(User $user): User
    {
        $this->push($user);

        return $user;
    }
}
