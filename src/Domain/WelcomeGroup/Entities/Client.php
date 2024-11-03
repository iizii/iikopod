<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use DateTimeInterface;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Client extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $comment,
        public readonly bool $blacklist,
        public readonly bool $patron,
        public readonly bool $vip,
        public readonly int $averageOrderSum,
        public readonly int $orderCount,
        public readonly DateTimeInterface $created,
        public readonly DateTimeInterface $updated,
        public readonly ?string $statusComment
    ) {}
}
