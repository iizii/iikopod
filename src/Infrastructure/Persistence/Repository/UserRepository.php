<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Repository;

use Domain\Users\Repository\UserRepositoryInterface;
use Shared\Persistence\Repository\AbstractPersistenceRepository;

final class UserRepository extends AbstractPersistenceRepository implements UserRepositoryInterface {}
